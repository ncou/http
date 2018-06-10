<?php

namespace Tests\Http\Psr\Integration;

use Http\Psr7Test\UploadedFileIntegrationTest;
use Chiron\Http\Factory\UploadedFileFactory;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    public function createSubject()
    {
        return (new UploadedFileFactory())->createUploadedFile('writing to tempfile');
    }
}
