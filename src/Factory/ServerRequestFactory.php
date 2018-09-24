<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

/*
require_once __DIR__ . '/../../../../vendor/nyholm/psr7/src/Uri.php';
*/

//github.com/http-interop/http-factory-diactoros/blob/master/src/ServerRequestFactory.php
//https://github.com/http-interop/http-factory-guzzle/blob/master/src/ServerRequestFactory.php
// https://github.com/http-interop/http-factory-slim/blob/master/src/ServerRequestFactory.php

// TODO : utiliser l'interface PSR17 : https://github.com/http-interop/http-factory/blob/master/src/ServerRequestFactoryInterface.php

// https://github.com/viserio/http-factory/blob/master/ServerRequestFactory.php
// https://github.com/zendframework/zend-diactoros/blob/master/src/ServerRequestFactory.php
// https://github.com/Wandu/Http/blob/master/Factory/ServerRequestFactory.php

//https://github.com/Hail-Team/framework/blob/fcd26224a6d175458df249b74bf03c88b5321840/src/Http/Helpers.php

//namespace Viserio\Component\HttpFactory;

use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

//use Nyholm\Psr7\Factory\ServerRequestFactory as ServerRequestFactoryPsr17;

// basé sur : https://github.com/viserio/http-factory/blob/master/ServerRequestFactory.php

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * Create a new server request.
     *
     * Note that server-params are taken precisely as given - no parsing/processing
     * of the given values is performed, and, in particular, no attempt is made to
     * determine the HTTP method or URI, which must be provided explicitly.
     *
     * @param string              $method       The HTTP method associated with the request.
     * @param UriInterface|string $uri          The URI associated with the request. If
     *                                          the value is a string, the factory MUST create a UriInterface
     *                                          instance based on it.
     * @param array               $serverParams Array of SAPI parameters with which to seed
     *                                          the generated request instance.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
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

        return new ServerRequest($method, $uri, $headers, $body, $protocolVersion, $serverParams);
    }
}
