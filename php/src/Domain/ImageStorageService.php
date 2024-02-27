<?php

namespace BrefStory\Domain;

use AsyncAws\S3\Result\PutObjectOutput;

interface ImageStorageService
{
    public function getImageFromBucket(int $imagePixels): ?array;

    public function saveImage(int $imagePixels, mixed $fetchedImage): void;

    public function createAndPutMetadata(int $imagePixels, array $metadata): PutObjectOutput;
}
