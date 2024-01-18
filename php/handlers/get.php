<?php

return function ($request, $context) {
    return \BrefStory\Application\ServiceFactory::createGetFibonacciImageHandler()
        ->handle($request, $context)
        ->toApiGatewayFormatV2();
};
