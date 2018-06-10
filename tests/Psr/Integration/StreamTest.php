<?php

namespace Tests\Http\Psr\Integration;

use Http\Psr7Test\StreamIntegrationTest;
use Chiron\Http\Factory\StreamFactory;

class StreamTest extends StreamIntegrationTest
{
    public function createStream($data)
    {
        return (new StreamFactory())->createStream($data);
    }
}
