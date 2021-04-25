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
        $this->container = $container; // TODO : virer cette ligne et faire plutot étendre la classe de ContainerAware pour que via la mutation on puisse alimenter le container ????
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

        // TODO : il faudrait pouvoir gérer ici le cas ou la réponse n'est pas une instance de ResponseInterface, mais par exemple une string et convertir cela en objet response via une classe de ResponseFactory.
        // https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Router/src/CoreHandler.php#L172
        //https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Http/src/CallableHandler.php#L47

        // TODO : faire un try/catch autour de la méthode handle et lever une response http 500 par défaut avec un message basique du genre "contact the administrator", ce message ne serait utiliser quand dans le cas ou l'utilisateur n'a pas ajouter de HttpErrorHandlerMiddleware pour gérer les erreurs http, donc ce cas ne devrait pas souvent arriver mais cela évite de dupliquer des try/catch dans tous les web dispatcher (SapiDispatcher / WorkermanDispatcher / RrDispatcher ...etc...).
        //https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Framework/Http/RrDispatcher.php#L101

        return $this->getPipeline()->handle($request);
    }
}
