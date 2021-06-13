<?php

declare(strict_types=1);

namespace Chiron\Http\Traits;

use Chiron\Container\Container;
use Chiron\Http\CallableHandler;
use Chiron\Http\MiddlewareBinding;
use Chiron\Pipeline\Pipeline;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\ContainerAwareInterface;
use Psr\Http\Message\ServerRequestInterface;

// TODO : passer les attributs et les méthodes en private ???

// TODO : indiquer dans la phpdoc que le handler fallback est facultatif (optional en anglais)

/**
 * The "container" attribute should be defined in the class to use this pipeline trait.
 */
trait PipelineTrait
{
    /** @ver Pipeline */
    protected $pipeline = null;
    /** @ver RequestHandlerInterface */
    protected $handler = null;
    /** @ver iterable<MiddlewareInterface> */
    protected $middlewares = [];

    // TODO : ajouter le typehint pour le paramétre de cette fonction !!!!
    // TODO : il faudrait pas ajouter un mécanisme pour éviter les doublons lorsqu'on ajoute un middleware ???? en vérifiant le get_class par exemple.
    protected function resolveMiddleware($middleware): MiddlewareInterface
    {
        $this->assertContainerInstance();

        if (is_string($middleware)) {
            // TODO : faire un catch de l'exception ContainerNotFoundException pour retourner une InvalidArgument ou PipelineException avec le message 'the string parameter is not a valid service name' ????
            $middleware = $this->buildClass($middleware);
        } elseif ($middleware instanceof MiddlewareBinding) {
            $parameters = $middleware->getParameters();
            // Resolve the middleware class name.
            $middleware = $this->buildClass($middleware->getClassName());
            // Should be a ParameterizedMiddlewareInterface::class instance.
            $middleware->setParameters($parameters);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        // TODO : créer une classe InvalidMiddlewareException ou PipelineException ???? ou ImproperlyConfiguredException !!!!
        // TODO : améliorer le message d'erreur !!!
        throw new InvalidArgumentException(sprintf(
            'Middleware "%s" is not an instance of %s',
            is_object($middleware) ? get_class($middleware) : gettype($middleware),
            MiddlewareInterface::class
        ));
    }

    // TODO : ATTENTION !!!!!!!! pour la paramétre $handler si c'est une string ca pourrait aussi être un callable sous forme de string style 'MyControler::index' ou 'ServiceName:method' c'est pas forcément un classname !!!! Puisque le CallableHandler supporte aussi les string en paramétre !!!!
    // TODO : ajouter le typehint pour le paramétre de cette fonction !!!!
    protected function resolveHandler($handler): RequestHandlerInterface
    {
        $this->assertContainerInstance();

        if (is_string($handler)) {
            $handler = $this->buildClass($handler);
        }

        if ($handler instanceof RequestHandlerInterface) {
            return $handler;
        }

        // TODO : ca serait pas plus logique de remplacer le is_object par un is_callable ????
        // Closure or invokable object, or an array to be resolved later in the CallableHandler.
        if (is_object($handler) || is_array($handler)) {
            return new CallableHandler($handler);
        }

        // TODO : créer une classe InvalidHandlerException ou PipelineException ???? ou ImproperlyConfiguredException !!!!
        throw new InvalidArgumentException(sprintf(
            'Handler "%s" is not a valid callable or an instance of %s',
            is_object($handler) ? get_class($handler) : gettype($handler),
            RequestHandlerInterface::class
        ));
    }

    protected function assertContainerInstance(): void
    {
        if (! $this->container instanceof Container) {
            // TODO : lever une missingcontainerexception !!!! et indiquer le nom de la classe ou on se trouve !!!!
            throw new InvalidArgumentException('Container instance is not set');
        }
    }

    protected function buildClass(string $className): object
    {
        // TODO : faire un catch de l'exception ContainerNotFoundException pour retourner une InvalidArgument ou PipelineException avec le message 'the string parameter is not a valid service name' ????
        // TODO : faire plutot un ->make() et ajouter un try/catch pour convertir les containerexception en ImproperlyConfiguredExcpetion ???
        return $this->container->get($className);
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
        // TODO : vérifier le container est bien présent sinon lever une exception ????


        // Use the cached pipeline if it's already instanciated.
        if ($this->pipeline) {
            return $this->pipeline;
        }

        //$this->pipeline = new Pipeline();
        $this->pipeline = $this->container->build(Pipeline::class);

        // TODO : code temporaire !!!!!!!!!!!!!!!!!!!!! le temps d'avoir un PSR14 Event Dispatcher (sur l'événement BeforeMiddleware::class). Attention il faudrait vérifier que le container est bien initialisé dans ce PipelineTrait !!!!
        /*
        $this->pipeline->beforeMiddleware = function ($request) {
            // Attach a fresh instance of the request in the container.
            $this->container->bind(ServerRequestInterface::class, $request);
        };*/


        // Add all the middlewares in the pipeline.
        foreach ($this->middlewares as $middleware) {
            if ($this->isContainerized($middleware)) { // TODO : rendre le code plus propre/lisible !!!
                $middleware->setContainer($this->container);
            }
            $this->pipeline->pipe($middleware);
        }

        // Add the final handler in the pipeline.
        if ($this->handler !== null) {
            if ($this->isContainerized($this->handler)) { // TODO : rendre le code plus propre/lisible !!!
                $this->handler->setContainer($this->container);
            }
            $this->pipeline->fallback($this->handler);
        }

        return $this->pipeline;
    }

    protected function isContainerized(object $class): bool
    {
        return $class instanceof ContainerAwareInterface && ! $class->hasContainer();
    }
}
