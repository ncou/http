<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\UriFactory;
use Http\Psr7Test\UriIntegrationTest;

class UriTest extends UriIntegrationTest
{
    public function createUri($uri)
    {
        return (new UriFactory())->createUri($uri);
    }
}
