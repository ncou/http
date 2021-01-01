<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\Container;
use Chiron\Container\SingletonInterface;
use Chiron\Http\Traits\PipelineTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplPriorityQueue;

final class Http implements RequestHandlerInterface, SingletonInterface
{
    use PipelineTrait;

    public const PRIORITY_MAX = 300;
    public const PRIORITY_HIGH = 200;
    public const PRIORITY_ABOVE_NORMAL = 100;
    public const PRIORITY_NORMAL = 0;
    public const PRIORITY_BELOW_NORMAL = -100;
    public const PRIORITY_LOW = -200;
    public const PRIORITY_MIN = -300;

    /** @var Container */
    private $container;
    /** @var int Seed used to ensure queue order for items of the same priority */
    private $serial = PHP_INT_MAX;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->middlewares = new SplPriorityQueue();
    }

    // TODO : ajouter le typehint des paramétres de cette fonction !!!
    // TODO : il faudrait pas ajouter un mécanisme pour éviter les doublons lorsqu'on ajoute un middleware ???? en vérifiant le get_class par exemple.
    public function addMiddleware($middleware, int $priority = self::PRIORITY_NORMAL): void
    {
        // Try to resolve the middleware by using the container.
        $middleware = $this->resolveMiddleware($middleware);
        // Use a priority int value during the middleware insertion in the queue.
        $this->middlewares->insert($middleware, [$priority, $this->serial--]);
    }

    // TODO : ajouter le typehint des paramétres de cette fonction !!!
    public function setHandler($handler): void
    {
        // Try to resolve the handler by using the container.
        $this->handler = $this->resolveHandler($handler);
    }

    /**
     * Use the Pipeline to iterate on a queue of middlewares/handler and execute them.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getPipeline()->handle($request);
    }
}
