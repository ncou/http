<?php
declare(strict_types=1);

namespace Chiron\Http\Factory;

use Interop\Http\Factory\ResponseFactoryInterface;
use Chiron\Http\Psr\Response;

class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(
        $statusCode = 200,
        $reasonPhrase = null,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ) {
        return new Response((int) $statusCode, $headers, $body, $protocolVersion, $reasonPhrase);
    }
}
