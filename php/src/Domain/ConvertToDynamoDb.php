<?php

namespace BrefStory\Domain;

use AsyncAws\DynamoDb\ValueObject\AttributeValue;

readonly class ConvertToDynamoDb
{
    public static function item(array $data): array
    {
        $dynamoData = [];
        foreach ($data as $key => $value) {
            $dynamoData[$key] = new AttributeValue(['S' => is_array($value) ? json_encode($value) : $value]);
        }
        return $dynamoData;
    }
}
