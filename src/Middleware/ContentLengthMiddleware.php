<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : regarder ici
// https://github.com/reactphp/http/blob/1.x/src/Io/StreamingServer.php#L264

//https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpFoundation/Response.php#L259

class ContentLengthMiddleware implements MiddlewareInterface
{
    /**
     * Add Content-Length header to the response if not already added previously.
     * According to RFC2616 section 4.4, we MUST ignore Content-Length: header if we are now receiving data using chunked Transfer-Encoding.
     *
     * @see http://www.ietf.org/rfc/rfc2616.txt
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Don't add the content-length header if transfert-encoding is present.
        if ($response->hasHeader('Transfer-Encoding')) {
            // And remove content-length if both headers are presents (according to RFC2616).
            if ($response->hasHeader('Content-Length')) {
                $response = $response->withoutHeader('Content-Length');
            }
        } elseif (! $response->hasHeader('Content-Length')) {
            $size = $response->getBody()->getSize();

            //die(var_dump($size));

            if ($size !== null) {
                $response = $response->withHeader('Content-Length', (string) $size);
            }
        }

        // TODO : retirer le content-type et le content-length si la réponse est empty (cad 204/205 ou 304)
        /*
        if ($this->isResponseEmpty($response)) {
            $response = $response
                ->withoutHeader('Content-Type')
                ->withoutHeader('Content-Length');
        }*/

        return $response;

        /*
                // Don't add the content-length header if transfert-encoding is present.
                if ($response->hasHeader('Transfer-Encoding')) {
                    if ($response->hasHeader('Content-Length')) {
                        // And remove content-length if both headers are presents (according to RFC2616).
                        return $response->withoutHeader('Content-Length');
                    }
                    return $response;
                }

                if ($response->hasHeader('Content-Length')) {
                    return $response;
                }

                $size = $response->getBody()->getSize();
                if ($size === null) {
                    return $response;
                }

                return $response->withHeader('Content-Length', (string) $size);
        */

                /*

                $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }

        return $headers;


                */
    }

    /*
     * Asserts response body is empty or status code is 204, 205 or 304
     *
     * @param ResponseInterface $response
     * @return bool
     */
    /*
    public function isResponseEmpty(ResponseInterface $response): bool
    {
        $contents = (string) $response->getBody();
        return empty($contents) || in_array($response->getStatusCode(), [204, 205, 304], true);
    }*/
}
