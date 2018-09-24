<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Chiron\Http\Psr\Request;
use Chiron\Http\Psr\Uri;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface
{
    /**
     * Create a new request.
     *
     * @param string              $method The HTTP method associated with the request.
     * @param UriInterface|string $uri    The URI associated with the request. If
     *                                    the value is a string, the factory MUST create a UriInterface
     *                                    instance based on it.
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        $headers = [];
        $body = null;
        $protocolVersion = '1.1';

        if (is_string($uri)) {
            $uri = new Uri($uri);
        }

        if (! $uri instanceof UriInterface) {
            throw new \InvalidArgumentException('Invalid URI provided; must be a string, or a Psr\Http\Message\UriInterface instance');
        }

        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }
}
