<?php

namespace BrefStory\Application;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\Result\PutItemOutput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use BrefStory\Domain\ImageMetadataItem;
use BrefStory\Domain\ImageRepository;
use BrefStory\Domain\ItemNotFound;

readonly class DynamoDbImageRepository implements ImageRepository
{
    public function __construct(
        private DynamoDbClient $client,
        private string $tableName,
    )
    {
    }

    /**
     * @throws ItemNotFound
     */
    public function findImage(int $imagePixels): ImageMetadataItem
    {
        $result = $this->client->getItem(new GetItemInput([
            'TableName' => $this->tableName,
            'ConsistentRead' => true,
            'Key' => [
                'PK' => new AttributeValue(['S' => 'IMAGE']),
                'SK' => new AttributeValue(['S' => "PIXELS#{$imagePixels}"]),
            ],
        ]));

        if (!$result->getItem()) {
            throw new ItemNotFound();
        }

        return ImageMetadataItem::fromDynamoDb($result->getItem());
    }

    public function addImageMetadata(ImageMetadataItem $imageMetadataItem): PutItemOutput
    {
        return $this->client->putItem(new PutItemInput([
            'TableName' => $this->tableName,
            'Item' => $imageMetadataItem->toDynamoDbItem(),
        ]));
    }
}
