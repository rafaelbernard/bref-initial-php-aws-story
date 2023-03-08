<?php

namespace BrefStory\Application;

use AsyncAws\S3\Exception\NoSuchKeyException;
use AsyncAws\S3\S3Client;
use BrefStory\Domain\ImageService;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PicsumPhotoService implements ImageService
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
    public function getImageFor(int $imagePixels): array
    {
        try {
            return $this->getImageFromBucket($imagePixels);
        } catch (NoSuchKeyException) {
            // do nothing
        }

        return $this->saveImageToBucket($imagePixels);
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
    private function saveImageToBucket(int $imagePixels): array
    {
        $response = $this->httpClient->request('GET', $url = "https://picsum.photos/{$imagePixels}");
        $output = $response->getContent();

        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $imageLocation = "images/$imagePixels.jpg",
            'Body' => $output,
        ]);

        $metadata = [
            'originalUrl' => $url,
            'redirectedUrl' => $response->getInfo()['url'] ?? null,
            'imageLocation' => $imageLocation,
            'contentDisposition' => $response->getHeaders()['content-disposition'] ?? null,
            'contentType' => $response->getHeaders()['content-type'] ?? null,
            'created' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => "metadata/$imagePixels.json",
            'Body' => json_encode($metadata),
        ]);

        return $metadata;
    }

    private function getImageFromBucket(int $imagePixels): ?array
    {
        $objectOutput = $this->s3Client->getObject([
            'Bucket' => $this->bucketName,
            'Key' => "metadata/$imagePixels.json",
        ]);

        return json_decode($objectOutput->getBody(), associative: true);
    }
}
