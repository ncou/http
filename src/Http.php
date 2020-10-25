<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\SingletonInterface;
use Chiron\Facade\HttpDecorator;
use Chiron\Pipe\Pipeline;
use Chiron\Routing\RoutingHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Container\BindingInterface;

// TODO : classe à déplacer dans le package chiron/routing ????

// https://github.com/cakephp/cakephp/blob/master/src/Http/Runner.php#L69
//https://github.com/cakephp/cakephp/blob/master/src/Http/MiddlewareQueue.php

// TODO : faire étendre cette classe de la classe Pipeline::class ????? ou fusionner le code ????
// TODO : utiliser une SplPriorityQueue pour ajouter des middlewares dans cette classe ????
// TODO : classe à renommer en HttpRunner ???? et ajouter une méthode run() qui effectue un reset de l'index à 0 et execute ensuite la méthode handle() [exemple : https://github.com/middlewares/utils/blob/master/src/Dispatcher.php#L44]
// TODO : créer un constructeur et lui passer l'objet MiddlewareDecorator, et utiliser la méthode decorate de cette classe lorsqu'on ajoute un middleware au tableau.
// TODO : renommer la classe en HttpRunner ou HttpKernel ou HttpHandler ou WebHandler
final class Http implements RequestHandlerInterface
{
    /** @var BindingInterface */
    private $binder;
    /** @var Pipeline */
    private $pipeline;

    public function __construct(BindingInterface $binder, MiddlewareQueue $middlewares)
    {
        $this->binder = $binder;
        $this->pipeline = new Pipeline($middlewares);
    }

    /**
     * @return Pipeline
     */
    public function getPipeline(): Pipeline
    {
        return $this->pipeline;
    }

    /**
     * Execute the middleware queue.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->bindRequestInstance($request);

        return $this->pipeline->handle($request);
    }

    /**
     * Store a "fresh" Request instance in the container.
     *
     * @param ServerRequestInterface $request
     */
    private function bindRequestInstance(ServerRequestInterface $request): void
    {
        // Requests are considered immutable, so a simple "bind()" is enough.
        $this->binder->bind(ServerRequestInterface::class, $request);
    }
}
