<?php

namespace BrefStory\Application;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
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
        private readonly DynamoDbClient $dynamoDbClient,
        private readonly string $tableName,
    )
    {
    }

    public function getJpegImageFor(int $imagePixels): array
    {
//        try {
//            return $this->getImageFromBucket($imagePixels);
//        } catch (NoSuchKeyException) {
//            // do nothing
//        }

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

    private function fetchImage(int $imagePixels): array
    {
        $response = $this->httpClient->request('GET', $url = "https://picsum.photos/{$imagePixels}");
        $fetchedImage = $response->getContent();
        return [$url, $response, $fetchedImage];
    }

    private function saveImage(int $imagePixels, mixed $fetchedImage): void
    {
        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->imageKeyFor($imagePixels),
            'Body' => $fetchedImage,
        ]);
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

        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->metadataKeyFor($imagePixels),
            'Body' => json_encode($metadata),
        ]);

        $result = $this->dynamoDbClient->putItem(new PutItemInput([
            'TableName' => $this->tableName,
            'Item' => [
                'PK' => new AttributeValue(['S' => 'IMAGE']),
                'SK' => new AttributeValue(['S' => "PIXELS#{$imagePixels}"]),
                ...self::toDynamoDbItem($metadata),
            ],
        ]));

        return $metadata;
    }

    private static function toDynamoDbItem(array $data): array
    {
        $dynamoData = [];
        foreach ($data as $key => $value) {
            $dynamoData[$key] = new AttributeValue(['S' => is_array($value) ? json_encode($value) : $value]);
            ServiceFactory::logger()->info('KV', compact('key', 'value'));
        }
        ServiceFactory::logger()->info('Meta', compact('dynamoData'));
        return $dynamoData;
    }

    private function imageKeyFor(int $imagePixels): string
    {
        return "image/$imagePixels.jpg";
    }

    private function metadataKeyFor(int $imagePixels): string
    {
        return "metadata/$imagePixels.json";
    }
}
