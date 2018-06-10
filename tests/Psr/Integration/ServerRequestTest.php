<?php

namespace Tests\Http\Psr\Integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use Chiron\Http\Factory\ServerRequestFactory;

class ServerRequestTest extends ServerRequestIntegrationTest
{
    public function createSubject()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        return (new ServerRequestFactory())->createServerRequestFromArray($_SERVER);
    }
}
