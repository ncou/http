<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Chiron\Http\Psr\Response;
use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory //implements ResponseFactoryInterface
{
    /**
     * Create a new response.
     *
     * @param int    $code         HTTP status code; defaults to 200
     * @param string $reasonPhrase Reason phrase to associate with status code
     *                             in generated response; if none is provided implementations MAY use
     *                             the defaults as suggested in the HTTP specification.
     *
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $headers = [];
        $body = null;
        $protocolVersion = '1.1';

        return new Response($code, $headers, $body, $protocolVersion, $reasonPhrase);
    }
}
