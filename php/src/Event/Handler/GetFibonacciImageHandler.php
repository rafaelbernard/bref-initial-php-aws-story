<?php

namespace BrefStory\Event\Handler;

use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Event\Http\HttpResponse;
use BrefStory\Application\PicsumPhotoService;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetFibonacciImageHandler implements Handler
{
    private const int MIN_PIXELS_FOR_REASONABLE_IMAGE_AND_NOT_BIG_FIBONACCI = 400;
    private const int MAX_PIXELS_FOR_REASONABLE_IMAGE_AND_NOT_BIG_FIBONACCI = 1000;

    public function __construct(private readonly PicsumPhotoService $photoService)
    {
    }

    public function handle($event, Context $context): HttpResponse
    {
        $int = (int) (
            $event['queryStringParameters']['int'] ?? random_int(
                self::MIN_PIXELS_FOR_REASONABLE_IMAGE_AND_NOT_BIG_FIBONACCI,
                self::MAX_PIXELS_FOR_REASONABLE_IMAGE_AND_NOT_BIG_FIBONACCI
            )
        );

        $metadata = $this->photoService->getJpegImageFor($int);

        $responseBody = [
            'context' => $context,
            'now' => $this->dateTimeImmutable()->format('Y-m-d H:i:s'),
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
