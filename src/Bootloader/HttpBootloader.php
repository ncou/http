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
use Chiron\Http\Middleware\SubFolderMiddleware;
use Chiron\Http\Middleware\CharsetByDefaultMiddleware;
use Chiron\Http\Middleware\ContentLengthMiddleware;
use Chiron\Core\Exception\ImproperlyConfiguredException;

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

        // TODO : ajouter ici le middleware subfolder cad avant le NotFoundDebugMiddleware sinon le uri->getPath() qui est affiché dans le template technical_404 ne sera pas bon, et donc on comprendra pas le debug !!!
        //$http->addMiddleware(SubFolderMiddleware::class, Http::PRIORITY_MAX - 2); // TODO : attention il faut pouvoir passer le basePath comme prefix à ce middleware. Ou null si on doit autodétecter le sous répertoire !!!!

        // TODO : voir si on ajoute vraiment ce middleware !!!
        //$http->addMiddleware(new CharsetByDefaultMiddleware($config->getDefaultCharset()), Http::PRIORITY_MAX - 2);

        // TODO : voir si on ajoute vraiment ce middleware !!!
        //$http->addMiddleware(ContentLengthMiddleware::class, Http::PRIORITY_MAX - 2);

        // TODO : ajouter ce middleware seulement si on est en mode APP_DEBUG === true

        // add the debugger for route not found middleware in the middleware stack.
        $http->addMiddleware(NotFoundDebugMiddleware::class, Http::PRIORITY_MAX - 3);

        // add the 'allowed hosts' middleware in the middleware stack.
        $http->addMiddleware(AllowedHostsMiddleware::class, Http::PRIORITY_MAX - 4);

        // assert the allowed hosts list is not empty, because the site will not work !
        if ($config->getAllowedHosts() === [] && $core->isDebug() === false) {
            throw new ImproperlyConfiguredException('http.ALLOWED_HOSTS list must not be empty in deployment.');
        }

        // add the defined middlewares with default priority.
        foreach ($config->getMiddlewares() as $middleware) {
            $http->addMiddleware($middleware, Http::PRIORITY_NORMAL);
        }
    }
}
