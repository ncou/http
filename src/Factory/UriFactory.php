<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Chiron\Http\Psr\Uri;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory //implements UriFactoryInterface
{
    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException If the given URI cannot be parsed.
     *
     * @return UriInterface
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
