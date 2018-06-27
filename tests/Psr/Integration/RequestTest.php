<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Psr\Request;
use Http\Psr7Test\RequestIntegrationTest;
use Chiron\Http\Psr\Uri;

class RequestTest extends RequestIntegrationTest
{
    public function createSubject()
    {
        return new Request('GET', new Uri('/'));
    }
}
