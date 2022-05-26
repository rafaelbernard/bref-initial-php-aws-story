<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    error_log('Writing to error-log');
    error_log(getenv('LAMBDA_TASK_ROOT'));
    error_log(getenv('APP_ENV'));
    error_log($context['APP_ENV']);
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
