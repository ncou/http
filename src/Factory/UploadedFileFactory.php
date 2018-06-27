<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

use Chiron\Http\Psr\UploadedFile;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @internal This class does not fall under our BC promise. We will adapt to changes to the http-interop/http-factory.
 * This class will be finalized when the PSR-17 is accepted.
 */
class UploadedFileFactory //implements UploadedFileFactoryInterface
{
    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the file.
     *
     * @see http://php.net/manual/features.file-upload.post-method.php
     * @see http://php.net/manual/features.file-upload.errors.php
     *
     * @param StreamInterface $stream Underlying stream representing the
     *     uploaded file content.
     * @param int $size in bytes
     * @param int $error PHP file upload error
     * @param string $clientFilename Filename as provided by the client, if any.
     * @param string $clientMediaType Media type as provided by the client, if any.
     *
     * @return UploadedFileInterface
     *
     * @throws \InvalidArgumentException If the file resource is not readable.
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        return new UploadedFile($stream, $stream->getSize(), $error, $clientFilename, $clientMediaType);
    }
}
