<?php

namespace BrefStory\Event\Handler;

use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Event\Http\HttpResponse;
use BrefStory\Application\PicsumPhotoService;
use BrefStory\Application\ServiceFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetFibonacciImageHandler implements Handler
//class GetFibonacciImageHandler
{
    public function __construct(private readonly PicsumPhotoService $photoService)
    {
    }

    public function handle($event, Context $context)
    {
        ServiceFactory::logger()->info('request', [$event]);

        $int = (int) ($event['queryStringParameters']['int'] ?? random_int(400, 1000));

        $metadata = $this->photoService->getJpegImageFor($int);

        $now = $this->dateTimeImmutable();

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

    protected function dateTimeImmutable(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
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
