<?php

namespace Chiron\Http\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\MiddlewareQueue;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;

final class MiddlewareQueueBootloader extends AbstractBootloader
{
    public function boot(MiddlewareQueue $middlewares, HttpConfig $config): void
    {
        // add the error handler middleware at the max top position in the middleware stack.
        if ($config->getHandleException() === true) {
            $middlewares->addMiddleware(ErrorHandlerMiddleware::class, MiddlewareQueue::PRIORITY_MAX);
        }

        // add the middlewares with default priority (second arg in the function "middleware").
        foreach ($config->getMiddlewares() as $middleware) {
            $middlewares->addMiddleware($middleware, MiddlewareQueue::PRIORITY_NORMAL);
        }
    }
}
