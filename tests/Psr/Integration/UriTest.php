<?php

namespace Tests\Http\Psr\Integration;

use Http\Psr7Test\UriIntegrationTest;
use Chiron\Http\Factory\UriFactory;

class UriTest extends UriIntegrationTest
{
    public function createUri($uri)
    {
        return (new UriFactory())->createUri($uri);
    }
}
