<?php

namespace BrefStory\Application;

use AsyncAws\S3\S3Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;

class SampleService
{
    public function getImageFor(int $imagePixels): string
    {
        $logger = new \Monolog\Logger('name');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

        $logger->info(__METHOD__);

        $client = HttpClient::create();
        $response = $client->request('GET', $url = "https://picsum.photos/{$imagePixels}");
        $output = $response->getContent();

        $logger->info('Info', [
            'bucketName' => getenv('BUCKET_NAME'),
            'image' => $image ?? null,
            'url' => $url ?? null,
            'response' => $response ? $response->getHeaders() : null,
        ]);

        $client = new S3Client();
        $client->putObject([
            'Bucket' => getenv('BUCKET_NAME'),
            'Key' => 'file-' . uniqid('file-', true) . '.jpg',
            'Body' => $output,
        ]);

        //die($output);
        return 'yes';
    }
}
