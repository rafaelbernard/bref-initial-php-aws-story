<?php

function APIResponse($body)
{
    $headers = array("Content-Type" => "application/json", "Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Headers" => "Content-Type", "Access-Control-Allow-Methods" => "OPTIONS,POST");
    return json_encode(array(
        "statusCode" => 200,
        "headers" => $headers,
        "body" => $body
    ));
}

return function () {
    error_log('To error log');

    //return new \Symfony\Component\HttpFoundation\JsonResponse(['OK. Time: ' . time()]);

    return APIResponse(['OK. Time: ' . time()]);
    return json_encode(['OK. Time: ' . time()]);
};
