<?php

namespace BrefStory\Domain;

use AsyncAws\DynamoDb\Result\PutItemOutput;

interface ImageRepository
{
    public function findImage(int $imagePixels): ImageMetadataItem;

    public function addImageMetadata(ImageMetadataItem $imageMetadataItem): PutItemOutput;
}
