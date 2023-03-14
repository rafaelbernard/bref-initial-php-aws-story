<?php

namespace BrefStory\Handler;

use Bref\Event\Http\HttpResponse;
use BrefStory\Application\PicsumPhotoService;
use BrefStory\Application\ServiceFactory;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetFibonacciImageHandler
{
    public function __construct(private readonly PicsumPhotoService $photoService)
    {
    }

    public function handle($request, \DateTimeImmutable $now = new \DateTimeImmutable()): HttpResponse
    {
        ServiceFactory::logger()->info('request', [$request]);

        $int = (int) ($request['queryStringParameters']['int'] ?? random_int(400, 1000));

        $metadata = $this->photoService->getJpegImageFor($int);

        $responseBody = [
            'response' => 'OK. Time: ' . $now->getTimestamp(),
            'now' => $now->format('Y-m-d H:i:s'),
            'int' => $int,
            'fibonacci' => $this->fibonacci($int),
            'metadata' => $metadata,
        ];

        $response = new JsonResponse($responseBody);

        return new HttpResponse($response->getContent(), $response->headers->all());
    }

    private function fibonacci(int $n): float
    {
        if ($n <= 1) {
            return $n;
        }

        $n2 = 0;
        $n1 = 1;

        for ($i = 2; $i < $n; $i++) {
            $n2_ = $n2;
            $n2 = $n1;
            $n1 = ($n1 + $n2_);
        }

        return $n2 + $n1;
    }
}
