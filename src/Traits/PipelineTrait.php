<?php

declare(strict_types=1);

namespace Chiron\Http\Traits;

use Chiron\Container\Container;
use Chiron\Http\CallableHandler;
use Chiron\Http\CallableMiddleware;
use Chiron\Http\MiddlewareBinding;
use Chiron\Pipeline\Pipeline;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\ContainerAwareInterface;
use Psr\Http\Message\ServerRequestInterface;

//https://github.com/yiisoft/middleware-dispatcher/blob/master/src/MiddlewareFactory.php#L154

// TODO : passer les attributs et les méthodes en private ???

// TODO : indiquer dans la phpdoc que le handler fallback est facultatif (optional en anglais)

/**
 * The "container" attribute should be defined in the class to use this pipeline trait.
 */
trait PipelineTrait
{
    /** @var Pipeline|null */
    protected $pipeline = null;
    /** @var RequestHandlerInterface|null */
    protected $handler = null;
    /** @var iterable<MiddlewareInterface> */
    protected $middlewares = [];

    // TODO : ajouter le typehint pour le paramétre de cette fonction !!!!
    // TODO : il faudrait pas ajouter un mécanisme pour éviter les doublons lorsqu'on ajoute un middleware ???? en vérifiant le get_class par exemple.
    // TODO : renommer en wrapXXX ou prepareXXX pour qu'on comprenne mieux à partir du nom de la fonction ce qu'elle va faire !!!!
    protected function resolveMiddleware($middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        // TODO : déplacer ce bout de code dans le constructeur de la classe CallableMiddleware ????
        if (is_string($middleware) && is_subclass_of($middleware, MiddlewareInterface::class)) {
            $middleware = [$middleware, 'process'];
        }

        // TODO : on devrait pas lever une exception si le $middleware résolu n'est pas du bon type ??? cad un type : callable|array|string, éventuellement utiliser la méthode Callback::isExecutable pour vérifier si le format du callable est correct !!!!

        return new CallableMiddleware($middleware);
    }

    // TODO : ajouter le typehint pour le paramétre de cette fonction !!!!
    // TODO : renommer en wrapXXX ou prepareXXX pour qu'on comprenne mieux à partir du nom de la fonction ce qu'elle va faire !!!!
    protected function resolveHandler($handler): RequestHandlerInterface
    {
        if ($handler instanceof RequestHandlerInterface) {
            return $handler;
        }

        // TODO : déplacer ce bout de code dans le constructeur de la classe CallableHandler ????
        if (is_string($handler) && is_subclass_of($handler, RequestHandlerInterface::class)) {
            $handler = [$handler, 'handle'];
        }

        // TODO : on devrait pas lever une exception si le $handler résolu n'est pas du bon type ??? cad un type : callable|array|string, éventuellement utiliser la méthode Callback::isExecutable pour vérifier si le format du callable est correct !!!!

        return new CallableHandler($handler);
    }

    /**
     * Initialize the pipeline with the middleware stack and the target handler.
     *
     * The 'string' middlewares are resolved as object using the container.
     * Optionnaly, the target handler is 'setted' with the container instance.
     *
     * @return Pipeline
     */
    // TODO : renommer en pipeline() ou preparePipeline() ou initPipeline() ou assemblePipeline() ????
    protected function getPipeline(): Pipeline
    {
        // Use the cached pipeline if it's already instanciated.
        if ($this->pipeline) {
            return $this->pipeline;
        }

        // TODO : vérifier le container est bien présent sinon lever une exception ????
        // TODO : vérifier le event dispatcher est bien présent sinon lever une exception ????

        //$this->pipeline = new Pipeline();
        $this->pipeline = $this->container->injector()->build(Pipeline::class);

        // Add all the middlewares in the pipeline.
        foreach ($this->middlewares as $middleware) {
            if ($this->isContainerized($middleware)) { // TODO : rendre le code plus propre/lisible !!! Passer par un mécanisme de Events ???
                $middleware->setContainer($this->container);
            }
            $this->pipeline->pipe($middleware);
        }

        // Add the final handler in the pipeline.
        if ($this->handler !== null) {
            if ($this->isContainerized($this->handler)) { // TODO : rendre le code plus propre/lisible !!! Passer par un mécanisme de Events ???
                $this->handler->setContainer($this->container);
            }
            $this->pipeline->fallback($this->handler);
        }

        return $this->pipeline;
    }

    // TODO : passer la méthode en private !!! la renommer en injectContainer() ou ensureContainerPresence() et insérer le container dans l'object directement dans cette méthode.
    protected function isContainerized(object $class): bool
    {
        return $class instanceof ContainerAwareInterface && ! $class->hasContainer();
    }
}
