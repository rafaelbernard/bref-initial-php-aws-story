<?php

namespace BrefStory\Domain;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;

readonly class ImageMetadataItem
{
    public function __construct(public int $imagePixels, public array $metadata)
    {
    }

    public function toDynamoDbItem(): array
    {
        return [
            'PK' => new AttributeValue(['S' => 'IMAGE']),
            'SK' => new AttributeValue(['S' => "PIXELS#{$this->imagePixels}"]),
            'pixels' => new AttributeValue(['N' => "{$this->imagePixels}"]),
            'metadata' => new AttributeValue(['S' => json_encode($this->metadata)]),
            ...ConvertToDynamoDb::item($this->metadata),
        ];
    }

    /**
     * @param array<string, AttributeValue> $item
     */
    public static function fromDynamoDb(array $item): static
    {
        return new static(
            (int) $item['pixels']->getN(),
            $item['metadata']->getS(),
        );
    }
}
