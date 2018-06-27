<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\UploadedFileFactory;
use Chiron\Http\Psr\Stream;
use Http\Psr7Test\UploadedFileIntegrationTest;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    public function createSubject()
    {
        $stream = new Stream(fopen('php://temp', 'wb+'));
        $stream->write('writing to tempfile');

        return (new UploadedFileFactory())->createUploadedFile($stream);
    }
}
