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
use Chiron\Event\EventDispatcher;
use Chiron\Http\Event\BeforeRequestEvent;
use Chiron\Http\Event\AfterRequestEvent;
use Chiron\Http\Event\ExceptionRaisedEvent;
use Throwable;
use Psr\Http\Message\ResponseFactoryInterface;

// TODO : améliorer le ResponseFacotryAware avec ce code : https://github.com/bemit/middleware-utils

//https://github.com/zendframework/zend-stdlib/blob/master/src/SplPriorityQueue.php

// TODO : utiliser un ContainerAwareTrait !!!
// TODO : créer un EventDispatcherAwareTrait ou un EventCapableTrait
final class Http implements RequestHandlerInterface, SingletonInterface
{
    use PipelineTrait;

    // TODO : externaliser ces constantes dans une classe séparée ? style Priority::MAX ou MiddlewarePriority::class
    public const PRIORITY_MAX = 300;
    public const PRIORITY_HIGH = 200;
    public const PRIORITY_ABOVE_NORMAL = 100;
    public const PRIORITY_NORMAL = 0;
    public const PRIORITY_BELOW_NORMAL = -100;
    public const PRIORITY_LOW = -200;
    public const PRIORITY_MIN = -300;

    /** @var Container */
    private $container;
    /** @var EventDispatcher */
    private $dispatcher;
    /** @var int Seed used to ensure queue order for items of the same priority */
    private $serial = PHP_INT_MAX;

    public function __construct(Container $container, EventDispatcher $dispatcher)
    {
        $this->container = $container; // TODO : virer cette ligne et faire plutot étendre la classe de ContainerAwareTrait pour que via la mutation on puisse alimenter le container ????
        $this->dispatcher = $dispatcher; // TODO : virer cette ligne et faire plutot étendre la classe de EventCapableTrait pour que via la mutation on puisse alimenter le dispatcher ????

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
        // TODO : lever un event EXCEPTION.RAISED dans le try/catch ?
        // TODO : faire un try/catch autour de la méthode handle et lever une response http 500 par défaut avec un message basique du genre "contact the administrator", ce message ne serait utiliser quand dans le cas ou l'utilisateur n'a pas ajouter de HttpErrorHandlerMiddleware pour gérer les erreurs http, donc ce cas ne devrait pas souvent arriver mais cela évite de dupliquer des try/catch dans tous les web dispatcher (SapiDispatcher / WorkermanDispatcher / RrDispatcher ...etc...).
        //https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Framework/Http/RrDispatcher.php#L101

        $this->dispatcher->dispatch(new BeforeRequestEvent($request));

        $pipeline = $this->getPipeline();

        // TODO : Vérifier dans les tests l'ordre d'appel de ces events en cas d'exception. Normalement l'ordre devrait être : BeforeRequest/ExceptionRaised/AfterRequest)
        try {
            return $response = $pipeline->handle($request);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch(new ExceptionRaisedEvent($e, $request));

            return $response = $this->handleException($e);
        } finally {
            $this->dispatcher->dispatch(new AfterRequestEvent($response));
        }
    }

    /**
     * @param \Throwable       $e
     *
     * @return ResponseInterface
     */
    // TODO : renommer en renderException() ????
    private function handleException(Throwable $e): ResponseInterface
    {
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse(500); // TODO : utiliser une constante !!!!

        // Reporting system (non handled) exception directly to the client.
        $response->getBody()->write('Unexpected exception. Try to catch exceptions in the middleware stack.');

        return $response;
    }
}
