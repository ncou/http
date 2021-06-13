<?php

declare(strict_types=1);

namespace Chiron\Http\Event;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;


final class ExceptionRaisedEvent
{
    /** @var Throwable */
    private $exception;
    /** @var ServerRequestInterface */
    private $request;

    /**
     * @param Throwable
     * @param ServerRequestInterface
     */
    public function __construct(Throwable $exception, ServerRequestInterface $request)
    {
        $this->exception = $exception;
        $this->request = $request;
    }

    /**
     * @return Throwable
     */
    public function getResponse(): Throwable
    {
        return $this->exception;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
