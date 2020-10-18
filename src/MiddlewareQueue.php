<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\SingletonInterface;
use Chiron\Facade\HttpDecorator;
use Chiron\Routing\RequestHandler;
use Chiron\Routing\RoutingHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplPriorityQueue;
use Chiron\Container\BindingInterface;

//https://github.com/zendframework/zend-stdlib/blob/master/src/SplPriorityQueue.php

// TODO : classe à déplacer dans le package chiron/pipeline ????
// TODO : code à nettoyer et à améliorer !!!!
final class MiddlewareQueue extends SplPriorityQueue implements SingletonInterface
{
    public const PRIORITY_MAX = 300;
    public const PRIORITY_HIGH = 200;
    public const PRIORITY_ABOVE_NORMAL = 100;
    public const PRIORITY_NORMAL = 0;
    public const PRIORITY_BELOW_NORMAL = -100;
    public const PRIORITY_LOW = -200;
    public const PRIORITY_MIN = -300;

    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    private $serial = PHP_INT_MAX;

    /**
     * @var array MiddlewareInterface[]
     */
    // TODO : attention le @var est faux, pour l'instant la variuable $stack peut contenir des callable, des string...etc
    //private $queue;

/*
    public function __construct()
    {
        //https://github.com/zendframework/zend-stdlib/blob/master/src/SplPriorityQueue.php
        $this->queue = new SplPriorityQueue();
    }
*/

    /**
     * Insert middleware in the queue with a given priority.
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middleware
     * @param int                                                                           $priority
     *
     * @return self
     */
    // TODO : remonter l'appel au HttpDecorator::toMiddleware() dans cette méthode ci dessous !!!!
    // TODO : ajouter une vérification pour ne pas insérer Xfois le même middleware, par contre si il y a une différence de priorité entre les 2 instertions c'est pas normal et donc il faudra lever une exception !!!! ou alors éventuellement remplacer automatiquement la priorité avec la plus élevée.
    // TODO : renommer la méthode en "insert()" car c'est une méthode définie dans l'objet pére SplPriorityQueue
    public function addMiddleware($middleware, int $priority = self::PRIORITY_NORMAL): void//: self
    {
        parent::insert($middleware, [$priority, $this->serial--]);

        //return $this;
    }





    /**
     * Add middleware to the beginning of the stack (Prepend).
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middlewares It could also be an array of such arguments.
     *
     * @return self
     */
    /*
    public function addMiddlewaresOnTop($middlewares): self
    {
        // Keep the right order when adding an array to the top of the middlewares stack.
        if (is_array($middlewares)) {
            $middlewares = array_reverse($middlewares);
        }

        return $this->add($middlewares, true);
    }*/

    /**
     * Add middleware to the bottom of the stack by default (Append).
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface|ResponseInterface $middlewares It could also be an array of such arguments.
     * @param bool                                                                          $onTop       Force the middleware position on top of the stack
     *
     * @return self
     */
    /*
    public function addMiddlewares($middlewares, bool $onTop = false): self
    {
        if (! is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        foreach ($middlewares as $middleware) {
            if ($onTop) {
                //prepend Middleware
                array_unshift($this->stack, $middleware);
            } else {
                // append Middleware
                array_push($this->stack, $middleware);
            }
        }

        return $this;
    }*/
}
