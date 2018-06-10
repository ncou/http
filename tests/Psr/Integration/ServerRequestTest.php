<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\ServerRequestFactory;
use Http\Psr7Test\ServerRequestIntegrationTest;

class ServerRequestTest extends ServerRequestIntegrationTest
{
    public function createSubject()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        return (new ServerRequestFactory())->createServerRequestFromArray($_SERVER);
    }
}
