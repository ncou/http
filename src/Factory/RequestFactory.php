<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Chiron\Http\Psr\Request;
use Interop\Http\Factory\RequestFactoryInterface;

class RequestFactory implements RequestFactoryInterface
{
    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }
}
