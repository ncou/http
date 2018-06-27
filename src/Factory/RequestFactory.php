<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Chiron\Http\Psr\Request;
use Interop\Http\Factory\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;

class RequestFactory //implements RequestFactoryInterface
{
    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request. If
     *     the value is a string, the factory MUST create a UriInterface
     *     instance based on it.
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        $headers = [];
        $body = null;
        $protocolVersion = '1.1';

        if (! $uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }

        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }
}
