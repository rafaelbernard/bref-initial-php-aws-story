<?php

namespace Test\Unit\BrefStory\Handler;

use Bref\Event\Http\HttpResponse;
use BrefStory\Application\PicsumPhotoService;
use BrefStory\Handler\GetFibonacciImageHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetFibonacciImageHandlerTest extends TestCase
{
    private PicsumPhotoService $picsumPhotoServiceMock;
    private GetFibonacciImageHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->picsumPhotoServiceMock = $this->createMock(PicsumPhotoService::class);

        $this->handler = new GetFibonacciImageHandler(
            $this->picsumPhotoServiceMock,
        );
    }

    public function testCanHandle()
    {
        $request = [
            'queryStringParameters' => ['int' => $int = 400],
        ];

        $this->picsumPhotoServiceMock->expects(self::once())
            ->method('getJpegImageFor')
            ->with($int)
            ->willReturn($metadata = ['metadata-key' => 'metadata-value']);

        $response = $this->handler->handle($request, $now = new \DateTimeImmutable());

        $symfonyResponse = new JsonResponse([
                'response' => 'OK. Time: ' . $now->getTimestamp(),
                'now' => $now->format('Y-m-d H:i:s'),
                'int' => $int,
                'fibonacci' => $this->fibonacci($int),
                'metadata' => $metadata,
            ]
        );

        $expected = new HttpResponse(
            $symfonyResponse->getContent(),
            $symfonyResponse->headers->all(),
        );

        self::assertEquals($expected, $response);
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
