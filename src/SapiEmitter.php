<?php

declare(strict_types=1);

namespace Chiron\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

//https://github.com/zendframework/zend-expressive-router/blob/master/src/Middleware/ImplicitHeadMiddleware.php
//https://github.com/zendframework/zend-expressive-router/blob/e76e6abd277c73268d27d92f7b385991e86488b9/test/Middleware/ImplicitHeadMiddlewareTest.php


// Lever des exception si les infos sont déjà émises (les headers ou si il y a déjà eu un echo de fait !!!) : https://github.com/Furious-PHP/http-runner/blob/master/src/Checker.php#L12   +   https://github.com/Furious-PHP/http-runner/tree/master/src/Exception    +    https://github.com/Furious-PHP/http-runner/blob/master/src/Runner.php#L22
// https://github.com/cakephp/cakephp/blob/master/src/Http/ResponseEmitter.php#L69

// TODO : Interface à virer elle ne sert pas à grand choses !!!!!
// TODO : ajouter une méthode public ->withoutBody(bool) ou 'shouldOutputBody(bool)' pour gérer le cas de la request méthode === GET, et pour ne pas passer ce booléen lors de la méthode emit, mais bien avant !!!!
// TODO : externaliser la méthode pour définir la tailler du buffer, elle pourra être appeller dans un bootloader pour modifier cette valeur.
final class SapiEmitter
{
    /** @var array list of http code who MUST not have a body */
    private const NO_BODY_RESPONSE_CODES = [204, 205, 304];

    /** @var int default buffer size (8Mb) */
    private const DEFAULT_BUFFER_SIZE = 8 * 1024 * 1024;

    /**
     * Construct the Emitter, and define the chunk size used to emit the body.
     *
     * @param int $bufferSize
     */
    public function __construct(int $bufferSize = self::DEFAULT_BUFFER_SIZE)
    {
        if ($bufferSize <= 0) {
            throw new InvalidArgumentException('Buffer size must be greater than zero');
        }

        $this->bufferSize = $bufferSize;
    }

    /**
     * Emit the http response to the client.
     *
     * @param ResponseInterface $response
     */
    // TODO : lever une exception si les headers sont déjà envoyés !!!!   https://github.com/yiisoft/yii-web/blob/master/src/SapiEmitter.php#L45
    // TODO : retourner void dans cette méthode emit, car le booléen ne sera jamais utilisé !!!!
    //https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Response.php#L391
    public function emit(ResponseInterface $response, bool $withoutBody = false): bool
    {
        //TODO : créer deux méthodes privées "sendHeaders" et "sendContent" comme dans symfony ? Cela merpettrait de regrouper la vérification du isResponseEmpty+ la ligne de code emitBody dans une sous fonction emitContent() par exemple !!!

        // TODO : renommer cette variable en $isEmpty
        $withoutBody = $withoutBody || $this->isResponseEmpty($response);

        // TODO : lever une exception si les headers sont déjà envoyés !!!!
        // headers have already been sent by the developer ?
        if (headers_sent() === false) {
            $this->emitHeaders($response);
            // It is important to mention that this method should be called after the headers are sent, in order to prevent PHP from changing the status code of the emitted response.
            $this->emitStatusLine($response);
        }

        if (! $withoutBody) {
            $this->emitBody($response);
        }

        return true;
    }

    /**
     * Send HTTP Headers.
     *
     * @param ResponseInterface $response
     */
    private function emitHeaders(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        foreach ($response->getHeaders() as $name => $values) {
            //$first = strtolower($name) !== 'set-cookie';
            $first = stripos($name, 'Set-Cookie') === 0 ? false : true;
            foreach ($values as $value) {
                $header = sprintf('%s: %s', $name, $value);
                header($header, $first, $statusCode);
                $first = false;
            }
        }
    }

    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is available, it, too, is emitted.
     *
     * It is important to mention that this method should be called after
     * `emitHeaders()` in order to prevent PHP from changing the status code of
     * the emitted response.
     */
    private function emitStatusLine(ResponseInterface $response): void
    {
        $statusLine = sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        header($statusLine, true, $response->getStatusCode());
    }

    /**
     * Emit the message body.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response to emit
     */
    private function emitBody(ResponseInterface $response): void
    {
        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (! $stream->eof()) {
            echo $stream->read($this->bufferSize);
            flush();
        }
    }

    /**
     * Asserts response body data is empty or http status code doesn't require a body.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    //https://github.com/yiisoft/yii-web/blob/master/src/SapiEmitter.php#L95
    private function isResponseEmpty(ResponseInterface $response): bool
    {
        if (in_array($response->getStatusCode(), self::NO_BODY_RESPONSE_CODES, true)) {
            return true;
        }

        $stream = $response->getBody();
        $seekable = $stream->isSeekable();
        if ($seekable) {
            $stream->rewind();
        }

        return $seekable ? $stream->read(1) === '' : $stream->eof();
    }

    /**
     * This is to be in compliance with RFC 2616, Section 9.
     * If the incoming request method is HEAD, we need to ensure that the response body
     * is empty as the request may fall back on a GET route handler due to FastRoute's
     * routing logic which could potentially append content to the response body
     * https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     */
    //https://github.com/slimphp/Slim/blob/4.x/Slim/App.php#L224
    /*
    $method = strtoupper($request->getMethod());
    if ($method === 'HEAD') {
        $emptyBody = $this->responseFactory->createResponse()->getBody();
        return $response->withBody($emptyBody);
    }*/
}
