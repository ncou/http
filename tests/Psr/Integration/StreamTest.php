<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\StreamFactory;
use Http\Psr7Test\StreamIntegrationTest;

class StreamTest extends StreamIntegrationTest
{
    public function createStream($data)
    {
        return (new StreamFactory())->createStream($data);
    }
}
