<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Message\StatusCode;
use Chiron\Http\Message\RequestMethod;

// TODO : regarder ici
// https://github.com/reactphp/http/blob/1.x/src/Io/StreamingServer.php#L264
//https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpFoundation/Response.php#L259

/**
 * Add Content-Length header to the response if not already added previously.
 *
 * @see http://www.ietf.org/rfc/rfc2616.txt
 * @see http://www.ietf.org/rfc/rfc7231.txt
 */
class ContentLengthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $code = $response->getStatusCode();
        $body = $response->getBody();
        $method = $request->getMethod();
        $version = $request->getProtocolVersion();

        // assign "Content-Length" header automatically
        $chunked = false;
        if (($method === RequestMethod::CONNECT && StatusCode::isSuccessful($code))
            || StatusCode::isInformational($code)
            || $code === StatusCode::NO_CONTENT) {
            // 2xx response to CONNECT and 1xx and 204 MUST NOT include Content-Length or Transfer-Encoding header
            $response = $response->withoutHeader('Content-Length');
        } elseif ($method === RequestMethod::HEAD && $response->hasHeader('Content-Length')) {
            // HEAD Request: preserve explicit Content-Length
        } elseif ($code === StatusCode::NOT_MODIFIED
            && ($response->hasHeader('Content-Length') || $body->getSize() === 0)) {
            // 304 Not Modified: preserve explicit Content-Length and preserve missing header if body is empty
        } elseif ($body->getSize() !== null) {
            // assign Content-Length header when using a "normal" buffered body string
            $response = $response->withHeader('Content-Length', (string) $body->getSize());
        } elseif (! $response->hasHeader('Content-Length') && $version === '1.1') {
            // assign chunked transfer-encoding if no 'content-length' is given for HTTP/1.1 responses
            $chunked = true;
        }

        // assign "Transfer-Encoding" header automatically
        if ($chunked) {
            $response = $response->withHeader('Transfer-Encoding', 'chunked');
        } else {
            // remove any Transfer-Encoding headers unless automatically enabled above
            $response = $response->withoutHeader('Transfer-Encoding');
        }

        return $response;
    }
}
