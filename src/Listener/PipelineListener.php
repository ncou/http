<?php

declare(strict_types=1);

namespace Chiron\Http\Listener;

use Chiron\Container\Container;
use Chiron\Pipeline\Event\BeforeMiddlewareEvent;
use Chiron\Pipeline\Event\BeforeHandlerEvent;
use Chiron\Event\ListenerInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PipelineListener implements ListenerInterface
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BeforeMiddlewareEvent::class,
            BeforeHandlerEvent::class,
        ];
    }

    /**
     * Attach a fresh instance of the request in the container.
     *
     * @param object $event An event class instance
     */
    public function process(object $event): void
    {
        $this->container->bind(ServerRequestInterface::class, $event->getRequest());
    }
}
