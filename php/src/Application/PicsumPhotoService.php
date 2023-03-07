<?php

namespace BrefStory\Application;

use AsyncAws\S3\S3Client;
use BrefStory\Domain\ImageService;
use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface $logger,
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
        $response = $this->httpClient->request('GET', $url = "https://picsum.photos/{$imagePixels}");
        $output = $response->getContent();

        $this->logger->info('Info', [
            'bucketName' => getenv('BUCKET_NAME'),
            'image' => $image ?? null,
            'url' => $url ?? null,
            'response' => $response->getHeaders(),
            'info' => $response->getInfo(),
        ]);

        $this->s3Client->putObject([
            'Bucket' => getenv('BUCKET_NAME'),
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
            'Bucket' => getenv('BUCKET_NAME'),
            'Key' => "metadata/$imagePixels.json",
            'Body' => json_encode($metadata),
        ]);

        return $metadata;
    }
}
