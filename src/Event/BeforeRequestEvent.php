<?php

declare(strict_types=1);

namespace Chiron\Http\Event;

use Psr\Http\Message\ServerRequestInterface;

/**
 * BeforeRequest event is raised before executing a request.
 */
final class BeforeRequestEvent
{
    /** @var ServerRequestInterface */
    private $request;

    /**
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
