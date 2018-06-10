<?php

namespace Tests\Http\Psr\Integration;

use Http\Psr7Test\ResponseIntegrationTest;
use Chiron\Http\Psr\Response;

class ResponseTest extends ResponseIntegrationTest
{
    public function createSubject()
    {
        return new Response();
    }
}
