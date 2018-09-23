<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\StreamFactory;
use Chiron\Http\Psr\Stream;
use Http\Psr7Test\StreamIntegrationTest;

class StreamTest extends StreamIntegrationTest
{
    public function createStream($content)
    {
        if (is_resource($content)) {
            return new Stream($content);
        }

        $resource = fopen('php://temp', 'rw+');
        $stream = new Stream($resource);
        $stream->write($content);

        return $stream;
    }
}
