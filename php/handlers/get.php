<?php

return function ($request) {
    return (new \BrefStory\Handler\GetFibonacciImageHandler(\BrefStory\Application\ServiceFactory::createPicsumPhotoService()))->handle($request)->toApiGatewayFormatV2();
};
