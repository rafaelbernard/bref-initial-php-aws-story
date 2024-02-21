<?php

namespace BrefStory\Application;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\S3\S3Client;
use BrefStory\Event\Handler\GetFibonacciImageHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class ServiceFactory
{
    public static function createGetFibonacciImageHandler(): GetFibonacciImageHandler
    {
        return new GetFibonacciImageHandler(
            self::createPicsumPhotoService()
        );
    }

    public static function createPicsumPhotoService(): PicsumPhotoService
    {
        return new PicsumPhotoService(
            HttpClient::create(),
            new S3Service(
                new S3Client(),
                getenv('BucketName'),
            ),
            new DynamoDbRepository(
                new DynamoDbClient(),
                getenv('TableName'),
            ),
        );
    }

    public static function logger(): LoggerInterface
    {
        static $logger;

        if (is_null($logger)) {
            $logger = new Logger('main');
            $logger->pushHandler(new StreamHandler('php://stdout', Level::Info));
        }

        return $logger;
    }
}
