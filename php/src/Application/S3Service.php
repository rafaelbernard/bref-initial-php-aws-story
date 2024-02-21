<?php

namespace BrefStory\Application;

use AsyncAws\S3\S3Client;

class S3Service
{
    public function __construct(
        private readonly S3Client $s3Client,
        private readonly string $bucketName,
    )
    {
    }

    public function getImageFromBucket(int $imagePixels): ?array
    {
        $objectOutput = $this->s3Client->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->metadataKeyFor($imagePixels),
        ]);

        return json_decode($objectOutput->getBody(), associative: true);
    }

    public function saveImage(int $imagePixels, mixed $fetchedImage): void
    {
        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->imageKeyFor($imagePixels),
            'Body' => $fetchedImage,
        ]);
    }

    public function createAndPutMetadata(int $imagePixels, array $metadata): \AsyncAws\S3\Result\PutObjectOutput
    {
        return $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->metadataKeyFor($imagePixels),
            'Body' => json_encode($metadata),
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
}
