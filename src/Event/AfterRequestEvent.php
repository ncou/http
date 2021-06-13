<?php

declare(strict_types=1);

namespace Chiron\Http\Event;

use Psr\Http\Message\ResponseInterface;

/**
 * AfterRequest event is raised after a request was executed.
 */
final class AfterRequestEvent
{
    /** @var ResponseInterface */
    private $response;

    /**
     * @param ResponseInterface
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
