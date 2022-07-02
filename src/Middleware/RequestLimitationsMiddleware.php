<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Http\Exception\Client\RequestHeaderFieldsTooLargeHttpException;
use Chiron\Http\Exception\Client\UriTooLongHttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Support\Str;

class RequestLimitationsMiddleware implements MiddlewareInterface
{
    // TODO : créer un constructeur qui initalise par défaut les valeurs du max uri length / header number...etc

    /**
     * Throw an HTTP 414 Exception if the URI is too long.
     * Throw an HTTP 431 Exception if the headers fields are too large.
     *
     * @param ServerRequestInterface  $request request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : Utiliser des constantes privées de classe pour les nombres ci dessous !!!!
        // *** Check limitation for the request uri length
        if (Str::byteLength($request->getServerParam('REQUEST_URI')) > 2048) {
            throw new UriTooLongHttpException();
        }

        // *** Check limitation for the maximum number of headers in the request
        if (count($request->getHeaders()) > 100) {
            throw new RequestHeaderFieldsTooLargeHttpException();
        }

        // *** Check limitation for the maximum size for all the headers
        if (Str::byteLength(serialize((array) $request->getHeaders())) > 4096) {
            throw new RequestHeaderFieldsTooLargeHttpException();
        }

        // *** Check limitations for each header maximum size (on the 'Name' and 'Value' header fields)
        foreach ($request->getHeaders() as $name => $values) {
            // Max allowed length for the header name
            if (Str::byteLength($name) > 64) {
                throw new RequestHeaderFieldsTooLargeHttpException();
            }

            // Max allowed length for the header value.
            if (Str::byteLength(serialize((array) $values)) > 2048) {
                throw new RequestHeaderFieldsTooLargeHttpException();
            }
        }

        return $handler->handle($request);
    }
}
