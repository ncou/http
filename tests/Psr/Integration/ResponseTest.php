<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Psr\Response;
use Http\Psr7Test\ResponseIntegrationTest;

class ResponseTest extends ResponseIntegrationTest
{
    public function createSubject()
    {
        return new Response();
    }
}
