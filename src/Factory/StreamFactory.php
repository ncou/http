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
class StreamFactory implements StreamFactoryInterface
{
    /**
     * @param string|resource $stream
     * @param string          $mode   Mode with which to open stream
     *
     * @throws InvalidArgumentException
     */
    public static function createFromStringOrResource($stream, $mode = 'r')
    {
        $error = null;
        $resource = $stream;

        if (is_string($stream)) {
            set_error_handler(function ($e) use (&$error) {
                $error = $e;
            }, E_WARNING);
            $resource = fopen($stream, $mode);
            restore_error_handler();
        }

        if ($error) {
            throw new InvalidArgumentException('Invalid stream reference provided');
        }

        if (! is_resource($resource) || 'stream' !== get_resource_type($resource)) {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }

        return new Stream($resource);
    }

    public function createStream($body = null)
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if ('resource' === gettype($body)) {
            return new Stream($body);
        }

        $resource = fopen('php://temp', 'rw+');
        $stream = new Stream($resource);
        $stream->write(null === $body ? '' : $body);
        // TODO : il faudra surement faire un rewind() non ?????
        return $stream;

    }

    /**
     * {@inheritdoc}
     *
     * @internal This function does not fall under our BC promise. We will adapt to changes to the http-interop/http-factory.
     * This class will be finalized when the PSR-17 is accepted.
     */
    public function createStreamFromFile($file, $mode = 'r')
    {
        $resource = fopen($file, $mode);

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     *
     * @internal This function does not fall under our BC promise. We will adapt to changes to the http-interop/http-factory.
     * This class will be finalized when the PSR-17 is accepted.
     */
    public function createStreamFromResource($resource)
    {
        return new Stream($resource);
    }
}
