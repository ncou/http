<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Chiron\Http\Psr\UploadedFile;
use Interop\Http\Factory\UploadedFileFactoryInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @internal This class does not fall under our BC promise. We will adapt to changes to the http-interop/http-factory.
 * This class will be finalized when the PSR-17 is accepted.
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(
        $stream,
        $size = null,
        $error = \UPLOAD_ERR_OK,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        return new UploadedFile($stream, $stream->getSize(), $error, $clientFilename, $clientMediaType);
    }
}
