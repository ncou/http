<?php

declare(strict_types=1);

namespace Chiron\Http\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Config\SettingsConfig;
use Chiron\Http\Http;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Http\Middleware\AllowedHostsMiddleware;
use Chiron\Core\Exception\BootException;

final class HttpBootloader extends AbstractBootloader
{
    public function boot(Http $http, HttpConfig $config, SettingsConfig $settings): void
    {
        // add the error handler middleware at the max top position in the middleware stack.
        if ($config->getHandleException() === true) {
            $http->addMiddleware(ErrorHandlerMiddleware::class, Http::PRIORITY_MAX);
        }

        // add the 'allowed hosts' middleware just after the error handler in the middleware stack.
        $http->addMiddleware(AllowedHostsMiddleware::class, Http::PRIORITY_MAX - 1);

        // assert the allowed hosts list is not empty, because the site will not work !
        if ($config->getAllowedHosts() === [] && $settings->isDebug() === false) {
            // TODO : créer une ImproperlyConfiguredException ou une BadConfigurationException ou une ConfigurationException dans le package chiron/core qui étendra de l'exception mére : BootException
            throw new BootException('http.ALLOWED_HOSTS list must not be empty in deployment.');
        }

        // add the defined middlewares with default priority.
        foreach ($config->getMiddlewares() as $middleware) {
            $http->addMiddleware($middleware, Http::PRIORITY_NORMAL);
        }
    }
}
