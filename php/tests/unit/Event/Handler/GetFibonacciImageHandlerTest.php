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

        $this->handler = $this
            ->getMockBuilder(GetFibonacciImageHandler::class)
            ->setConstructorArgs([$this->picsumPhotoServiceMock])
            ->onlyMethods(['dateTimeImmutable'])
            ->getMock();
    }

    public function testCanHandle()
    {
        $int = 400;
        $fibonacciFromInt = 1.760236806450138e+83;
        $request = ['queryStringParameters' => ['int' => $int]];
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
                'fibonacci' => $fibonacciFromInt,
                'metadata' => $metadata,
            ]
        );

        $expected = new HttpResponse(
            $symfonyResponse->getContent(),
            $symfonyResponse->headers->all(),
        );

        self::assertEquals($expected, $response);
    }
}
