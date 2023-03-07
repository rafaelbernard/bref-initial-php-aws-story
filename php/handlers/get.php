<?php
function fibonacci(int $n): float
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

return function ($request) {
    $int = (int) ($request['queryStringParameters']['int'] ?? random_int(400, 1200));

    $metadata = \BrefStory\Application\ServiceFactory::createPicsumPhotoService()->getImageFor($int);

    $responseBody = [
        'response' => 'OK. Time: ' . time(),
        'now' => date('Y-m-d H:i:s'),
        'int' => $int,
        'fibonacci' => fibonacci($int),
        'metadata' => $metadata,
    ];

    $response = new \Symfony\Component\HttpFoundation\JsonResponse($responseBody);

    return (new \Bref\Event\Http\HttpResponse($response->getContent(), $response->headers->all()))->toApiGatewayFormatV2();
};
