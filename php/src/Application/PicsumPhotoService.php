<?php

namespace BrefStory\Application;

use AsyncAws\S3\Exception\NoSuchKeyException;
use AsyncAws\S3\S3Client;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PicsumPhotoService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly S3Client $s3Client,
        private readonly string $bucketName,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getJpegImageFor(int $imagePixels): array
    {
        try {
            return $this->getImageFromBucket($imagePixels);
        } catch (NoSuchKeyException) {
            // do nothing
        }

        return $this->fetchAndSaveImageToBucket($imagePixels);
    }

    private function getImageFromBucket(int $imagePixels): ?array
    {
        $objectOutput = $this->s3Client->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->metadataKeyFor($imagePixels),
        ]);

        return json_decode($objectOutput->getBody(), associative: true);
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

    /**
     * @param int $imagePixels
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function fetchImage(int $imagePixels): array
    {
        $response = $this->httpClient->request('GET', $url = "https://picsum.photos/{$imagePixels}");
        $fetchedImage = $response->getContent();
        return [$url, $response, $fetchedImage];
    }

    /**
     * @param int $imagePixels
     * @param mixed $fetchedImage
     *
     * @return void
     */
    public function saveImage(int $imagePixels, mixed $fetchedImage): void
    {
        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->imageKeyFor($imagePixels),
            'Body' => $fetchedImage,
        ]);
    }

    private function imageKeyFor(int $imagePixels): string
    {
        return "image/$imagePixels.jpg";
    }

    private function metadataKeyFor(int $imagePixels): string
    {
        return "metadata/$imagePixels.json";
    }

    /**
     * @param mixed $url
     * @param mixed $response
     * @param int $imagePixels
     *
     * @return array
     */
    public function createAndPutMetadata(mixed $url, ResponseInterface $response, int $imagePixels): array
    {
        $metadata = [
            'originalUrl' => $url,
            'redirectedUrl' => $response->getInfo()['url'] ?? null,
            'imageLocation' => $this->imageKeyFor($imagePixels),
            'contentDisposition' => $response->getHeaders()['content-disposition'] ?? null,
            'contentType' => $response->getHeaders()['content-type'] ?? null,
            'created' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->metadataKeyFor($imagePixels),
            'Body' => json_encode($metadata),
        ]);
        return $metadata;
    }
}
