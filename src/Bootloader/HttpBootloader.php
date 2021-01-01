<?php

declare(strict_types=1);

namespace Chiron\Http\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Http;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;

final class HttpBootloader extends AbstractBootloader
{
    public function boot(Http $http, HttpConfig $config): void
    {
        // add the error handler middleware at the max top position in the middleware stack.
        if ($config->getHandleException() === true) {
            $http->addMiddleware(ErrorHandlerMiddleware::class, Http::PRIORITY_MAX);
        }

        // add the middlewares with default priority (second arg in the function "middleware").
        foreach ($config->getMiddlewares() as $middleware) {
            $http->addMiddleware($middleware, Http::PRIORITY_NORMAL);
        }
    }
}
