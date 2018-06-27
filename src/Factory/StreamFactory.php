<?php

declare(strict_types=1);

namespace Chiron\Http\Factory;

//https://github.com/http-interop/http-factory/blob/master/src/StreamFactoryInterface.php

// TODO : regarder aussi ici comment c'est fait : https://github.com/akrabat/rka-content-type-renderer/blob/master/src/SimplePsrStream.php
// https://github.com/akrabat/Slim-Http/blob/master/src/Stream.php

/*
 * @see       https://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

//namespace Zend\Diactoros;

use Chiron\Http\Psr\Stream;
use Interop\Http\Factory\StreamFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * Implementation of PSR HTTP streams.
 */
class StreamFactory //implements StreamFactoryInterface
{
    /**
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param string $content String content with which to populate the stream.
     *
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'rw+');
        $stream = new Stream($resource);
        if (! empty($content)) {
            $stream->write($content);
            $stream->rewind();
        }

        return $stream;
    }

    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode
     * supported by the `fopen` function.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @param string $filename Filename or stream URI to use as basis of stream.
     * @param string $mode Mode with which to open the underlying filename/stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);

        return new Stream($resource);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param resource $resource PHP resource to use as basis of stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
