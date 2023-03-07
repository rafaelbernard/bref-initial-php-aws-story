<?php

namespace BrefStory\Application;
use AsyncAws\S3\S3Client;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;

class ServiceFactory
{
    public static function createPicsumPhotoService(): PicsumPhotoService
    {
        $logger = new Logger('main');
        $logger->pushHandler(new StreamHandler('php://stdout', Level::Info));

        return new PicsumPhotoService(
            HttpClient::create(),
            new S3Client(),
            $logger,
        );
    }
}
