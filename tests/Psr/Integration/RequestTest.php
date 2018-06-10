<?php

namespace Tests\Http\Psr\Integration;

use Http\Psr7Test\RequestIntegrationTest;
use Chiron\Http\Psr\Request;

class RequestTest extends RequestIntegrationTest
{
    public function createSubject()
    {
        return new Request('GET', '/');
    }
}
