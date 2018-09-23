<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\UriFactory;
use Chiron\Http\Psr\Uri;
use Http\Psr7Test\UriIntegrationTest;

class UriTest extends UriIntegrationTest
{
    public function createUri($uri)
    {
        return new Uri($uri);
    }
}
