<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

// TODO : example : https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/RouteMiddleware.php
// TODO : regarder ici https://github.com/zrecore/Spark/blob/master/src/Handler/RouteHandler.php    et https://github.com/equip/framework/blob/master/src/Handler/DispatchHandler.php

//https://github.com/zendframework/zend-expressive-router/blob/8983c9422277ab30bc1185bfbbb72930837c8206/src/Middleware/ImplicitHeadMiddleware.php
//https://github.com/mezzio/mezzio-router/blob/3.2.x/src/Middleware/ImplicitHeadMiddleware.php

//use Chiron\Http\Psr\Response;
use Chiron\Http\Message\RequestMethod;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This middleware is enforce compliance with RFC 2616, Section 9.
 * If the incoming request method is HEAD, we need to ensure that the response body
 * is empty as the request may fall back on a GET route handler due to FastRoute's
 * routing logic which could potentially append content to the response body
 * https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
 */

// TODO : à virer car on gérer le cas du HEAD dans le SapiEngine car on on ne va pas emettre le body dans le SapiEmitter !!!
//https://github.com/slimphp/Slim/blob/4.x/Slim/App.php#L224
final class HeadMethodMiddleware implements MiddlewareInterface
{
    /** @var StreamFactoryInterface */
    private $streamFactory;

    // TODO : passer en paramétre une responsefactory et un streamfactory.
    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    /**
     * This is to be in compliance with RFC 2616, Section 9.
     * If the incoming request method is HEAD, we need to ensure that the response body
     * is empty as the request may fall back on a GET route handler due to FastRoute's
     * routing logic which could potentially append content to the response body
     * https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     */
    //https://github.com/slimphp/Slim/blob/4.x/Slim/App.php#L224
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Execute the next handler
        $response = $handler->handle($request);

        // As per RFC, HEAD request can't have a body.
        if (strtoupper($request->getMethod()) === RequestMethod::HEAD) {
            // TODO : il faudrait surement enlever le ContentType et le Content-Lenght ? non ???? Hummm je pense que le content-length est à garder car il simule la taille de la page qui aurait du être affichée.
            $emptyBody = $this->streamFactory->createStream();
            $response = $response->withBody($emptyBody);
        }

        return $response;
    }
}
