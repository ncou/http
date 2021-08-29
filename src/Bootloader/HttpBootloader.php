<?php

declare(strict_types=1);

namespace Chiron\Http\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Core\Core;
use Chiron\Http\Http;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Http\Middleware\AllowedHostsMiddleware;
use Chiron\Http\Middleware\NotFoundDebugMiddleware;
use Chiron\Http\Middleware\TagRequestMiddleware;
use Chiron\Core\Exception\BootException;

final class HttpBootloader extends AbstractBootloader
{
    public function boot(Http $http, HttpConfig $config, Core $core): void
    {
        // add the error handler middleware at the max top position in the middleware stack.
        if ($config->getHandleException() === true) {
            $http->addMiddleware(ErrorHandlerMiddleware::class, Http::PRIORITY_MAX);
        }

        // TODO : vérifier si il devrait pas être avant le error handler car il se peut que la réponse générée par le errorhandler ne contienne plus le tagid car on fait un new Response dans le classe du  error handler !!!!
        // add the middleware to attach a unique id in the request attributes.
        if ($config->getTagRequest() === true) {
            $http->addMiddleware(TagRequestMiddleware::class, Http::PRIORITY_MAX - 1);
        }

        // add the debugger for route not found middleware in the middleware stack.
        $http->addMiddleware(NotFoundDebugMiddleware::class, Http::PRIORITY_MAX - 2);

        // add the 'allowed hosts' middleware in the middleware stack.
        $http->addMiddleware(AllowedHostsMiddleware::class, Http::PRIORITY_MAX - 3);

        // assert the allowed hosts list is not empty, because the site will not work !
        if ($config->getAllowedHosts() === [] && $core->isDebug() === false) {
            // TODO : créer une ImproperlyConfiguredException ou une BadConfigurationException ou une ConfigurationException dans le package chiron/core qui étendra de l'exception mére : BootException
            // TODO : code désactivé temporairement car lors de l'installation de l'application, comme on n'a pas encore de fichier .env le debug est mis par défaut à false !!!
            throw new BootException('http.ALLOWED_HOSTS list must not be empty in deployment.');
        }

        // add the defined middlewares with default priority.
        foreach ($config->getMiddlewares() as $middleware) {
            $http->addMiddleware($middleware, Http::PRIORITY_NORMAL);
        }
    }
}
