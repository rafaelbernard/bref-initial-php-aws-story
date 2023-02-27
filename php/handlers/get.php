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
    $int = (int) ($request['queryStringParameters']['int'] ?? random_int(1, 300));

    $responseBody = [
        'response' => 'OK. Time: ' . time(),
        'now' => date('Y-m-d H:i:s'),
        'int' => $int,
        'result' => fibonacci($int),
    ];

    $response = new \Symfony\Component\HttpFoundation\JsonResponse($responseBody);

    error_log('Log!');

    $image = (new \BrefStory\Application\SampleService())->getImageFor($int);

    return (new \Bref\Event\Http\HttpResponse($response->getContent(), $response->headers->all()))->toApiGatewayFormatV2();
};
