<?php

namespace Test\Unit\BrefStory\Event\Handler;

use Bref\Context\Context;
use Bref\Event\Http\HttpResponse;
use BrefStory\Application\PicsumPhotoService;
use BrefStory\Event\Handler\GetFibonacciImageHandler;
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

//        $this->handler = new GetFibonacciImageHandler(
//            $this->picsumPhotoServiceMock,
//        );

        $this->handler = $this
            ->getMockBuilder(GetFibonacciImageHandler::class)
            ->setConstructorArgs([$this->picsumPhotoServiceMock])
            ->onlyMethods(['dateTimeImmutable'])
            ->getMock();
    }

    public function testCanHandle()
    {
        $request = ['queryStringParameters' => ['int' => $int = 400]];
        $context = new Context('fake-aws-id', 123456, 'fake-invoked-arn', 'fake-traceid');
        $now = new \DateTimeImmutable();
        $metadata = ['metadata-key' => 'metadata-value'];

        $this->handler->expects(self::once())
            ->method('dateTimeImmutable')
            ->willReturn($now);

        $this->picsumPhotoServiceMock->expects(self::once())
            ->method('getJpegImageFor')
            ->with($int)
            ->willReturn($metadata);

        $response = $this->handler->handle($request, $context);

        $symfonyResponse = new JsonResponse([
                'context' => $context,
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
