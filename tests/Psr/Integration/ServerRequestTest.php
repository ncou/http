<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\ServerRequestFactory;
use Chiron\Http\Psr\Uri;
use Chiron\Http\Psr\ServerRequest;
use Http\Psr7Test\ServerRequestIntegrationTest;

class ServerRequestTest extends ServerRequestIntegrationTest
{
    public function createSubject()
    {
        return new ServerRequest('GET', new Uri('/'), [], null, '1.1', $_SERVER);
    }
}
