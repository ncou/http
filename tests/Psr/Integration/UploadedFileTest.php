<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Psr\Stream;
use Chiron\Http\Psr\UploadedFile;
use Http\Psr7Test\UploadedFileIntegrationTest;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    public function createSubject()
    {
        $stream = new Stream(fopen('php://temp', 'wb+'));
        $stream->write('writing to tempfile');

        return new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK);
    }
}
