<?php

namespace Tests\Http\Psr\Integration;

use Chiron\Http\Factory\UploadedFileFactory;
use Http\Psr7Test\UploadedFileIntegrationTest;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    public function createSubject()
    {
        return (new UploadedFileFactory())->createUploadedFile('writing to tempfile');
    }
}
