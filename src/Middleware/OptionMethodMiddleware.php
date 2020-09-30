<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

// TODO : example : https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/RouteMiddleware.php
// TODO : regarder ici https://github.com/zrecore/Spark/blob/master/src/Handler/RouteHandler.php    et https://github.com/equip/framework/blob/master/src/Handler/DispatchHandler.php

//https://github.com/zendframework/zend-expressive-router/blob/8983c9422277ab30bc1185bfbbb72930837c8206/src/Middleware/ImplicitOptionsMiddleware.php
//https://github.com/mezzio/mezzio-router/blob/3.2.x/src/Middleware/ImplicitOptionsMiddleware.php

use Chiron\Http\Exception\Client\MethodNotAllowedHttpException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Message\RequestMethod;

class OptionMethodMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    // TODO : passer en paramétre une responsefactory et un streamfactory.
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (MethodNotAllowedHttpException $exception) {
            // The OPTIONS request should send back a response with some headers like "Allow" header.
            // TODO : vérifier le comportement avec CORS.
            // TODO : regarder si ce code peut aider : https://github.com/illuminate/routing/blob/master/RouteCollection.php#L234

            // TODO : https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/ImplicitOptionsMiddleware.php#L88
            if ($request->getMethod() === RequestMethod::OPTIONS) {
                // TODO : créer une méthode getAllowedMethods() dans la class MethodNotAllowedHttpException.
                // TODO : vérifier si il n'est pas possible de forcer le code à 200 au lieu de 405 dans le cas de cette erreur !!! comme ca c'est le middleware ErrorHandler qui se chargera de créer la réponse.
                return ($this->responseFactory->createResponse())->withHeader('Allow', $exception->getHeaders()['Allow']);
            }

            throw $exception;
        }

        return $response;
    }
}
