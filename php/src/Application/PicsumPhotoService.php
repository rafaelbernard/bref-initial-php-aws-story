<?php

namespace BrefStory\Application;

use AsyncAws\S3\Exception\NoSuchKeyException;
use BrefStory\Domain\ImageMetadataItem;
use BrefStory\Domain\ItemNotFound;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class PicsumPhotoService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private S3Service $s3Service,
        private DynamoDbRepository $repository,
    )
    {
    }

    public function getJpegImageFor(int $imagePixels): array
    {
        try {
            return $this->getImage($imagePixels);
        } catch (NoSuchKeyException|ItemNotFound) {
            ServiceFactory::logger()->info('Not found. Will create.');
            // do nothing
        }

        return $this->fetchAndSaveImageToBucket($imagePixels);
    }

    /**
     * @throws ItemNotFound
     */
    private function getImage(int $imagePixels): ?array
    {
        $this->repository->findImage($imagePixels);
        return $this->s3Service->getImageFromBucket($imagePixels);
    }

    /**
     * @param int $imagePixels
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function fetchAndSaveImageToBucket(int $imagePixels): array
    {
        list($url, $response, $fetchedImage) = $this->fetchImage($imagePixels);

        $this->saveImage($imagePixels, $fetchedImage);

        return $this->createAndPutMetadata($url, $response, $imagePixels);
    }

    private function fetchImage(int $imagePixels): array
    {
        $response = $this->httpClient->request('GET', $url = "https://picsum.photos/{$imagePixels}");
        $fetchedImage = $response->getContent();
        return [$url, $response, $fetchedImage];
    }

    private function saveImage(int $imagePixels, mixed $fetchedImage): void
    {
        $this->s3Service->saveImage($imagePixels, $fetchedImage);
    }

    private function createAndPutMetadata(mixed $url, ResponseInterface $response, int $imagePixels): array
    {
        $metadata = [
            'originalUrl' => $url,
            'redirectedUrl' => $response->getInfo()['url'] ?? null,
            'imageLocation' => $this->imageKeyFor($imagePixels),
            'contentDisposition' => $response->getHeaders()['content-disposition'] ?? null,
            'contentType' => $response->getHeaders()['content-type'] ?? null,
            'created' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        $this->s3Service->createAndPutMetadata($imagePixels, $metadata);

        $this->repository->addImageMetadata(new ImageMetadataItem($imagePixels, $metadata));

        return $metadata;
    }

    private function imageKeyFor(int $imagePixels): string
    {
        return "image/$imagePixels.jpg";
    }
}
