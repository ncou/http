<?php

declare(strict_types=1);

namespace Chiron\Http\Traits;

use Psr\Http\Server\MiddlewareInterface;
use Chiron\Http\CallableHandler;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Pipeline\Pipeline;
use InvalidArgumentException;

// TODO : mettre cette classe dans un répertoire "Traits" ????
// TODO : passer les attributs et les méthodes en private ???

// TODO : déplacer la classe PipelineTrait et CallableHandler dans le package chiron/http (respectivement dans un sous répertoire xxx/Traits et xxx/Handler)

/**
 * The "container" should be defined in the class to use this pipeline trait.
 */
trait PipelineTrait
{
    /** @ver Pipeline */
    protected $pipeline = null;
    /** @ver RequestHandlerInterface */
    protected $handler = null;
    /** @ver iterable<MiddlewareInterface> */
    protected $middlewares = [];

    protected function resolveMiddleware($middleware): MiddlewareInterface
    {
        if (is_string($middleware)) {
            // TODO : faire un catch de l'exception ContainerNotFoundException pour retourner une InvalidArgument ou PipelineException avec le message 'the string parameter is not a valid service name' ????
            $middleware = $this->container->get($middleware); // TODO : faire plutot un ->make()
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        // TODO : créer une classe InvalidMiddlewareException ou PipelineException ????
        throw new InvalidArgumentException(sprintf(
            'Middleware "%s" is not an instance of %s',
            is_object($middleware) ? get_class($middleware) : gettype($middleware),
            MiddlewareInterface::class
        ));
    }

    protected function resolveHandler($handler): RequestHandlerInterface
    {
        if (is_string($handler)) {
            // TODO : faire un catch de l'exception ContainerNotFoundException pour retourner une InvalidArgument ou PipelineException avec le message 'the string parameter is not a valid service name' ????
            $handler = $this->container->get($handler); // TODO : faire plutot un ->make()
        }

        if ($handler instanceof RequestHandlerInterface) {
            return $handler;
        }

        // Closure or invokable object, or an array to be resolved later in the CallableHandler.
        if (is_object($handler) || is_array($handler)) {
            return new CallableHandler($handler);
        }

        // TODO : créer une classe InvalidHandlerException ou PipelineException ????
        throw new InvalidArgumentException(sprintf(
            'Handler "%s" is not a callable or an instance of %s',
            is_object($handler) ? get_class($handler) : gettype($handler),
            RequestHandlerInterface::class
        ));
    }

    /**
     * Initialize the pipeline with the middleware stack and the target handler.
     *
     * The 'string' middlewares are resolved as object using the container.
     * Optionnaly, the target handler is 'setted' with the container instance.
     *
     * @return Pipeline
     */
    protected function getPipeline(): Pipeline
    {
        // Use the cached pipeline if it's already instanciated.
        if ($this->pipeline) {
            return $this->pipeline;
        }

        $this->pipeline = new Pipeline($this->container);

        // Add all the middlewares in the pipeline.
        foreach ($this->middlewares as $middleware) {
            $this->pipeline->pipe($middleware);
        }
        // Add the final handler in the pipeline.
        if ($this->handler) {
            $this->pipeline->fallback($this->handler);
        }

        return $this->pipeline;
    }
}