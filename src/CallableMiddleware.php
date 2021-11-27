<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\ContainerAwareInterface;
use Chiron\Container\ContainerAwareTrait;
use Chiron\Http\Exception\Client\BadRequestHttpException;
use Chiron\Injector\Exception\InvocationException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Callback wraps arbitrary PHP callback into object matching [[MiddlewareInterface]].
 * Usage example:
 *
 * ```php
 * $middleware = new CallbackMiddleware(function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
 *     if ($request->getParams() === []) {
 *         return new Response();
 *     }
 *     return $handler->handle($request);
 * });
 * $response = $middleware->process(Yii::$app->getRequest(), $handler);
 * ```
 *
 * @see MiddlewareInterface
 */
final class CallableMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var callable|array|string a PHP callback matching signature of [RequestHandlerInterface->handle(ServerRequestInterface $request)]]. // TODO : non c'est faux ce n'est pas obligatoirement une signature de type requesthandler !!!!
     */
    protected $callable;

    /**
     * @param callable|array|string $callable A PHP callback matching signature of [RequestHandlerInterface->handle(ServerRequestInterface $request)]]. // TODO : non c'est faux ce n'est pas obligatoirement une signature de type requesthandler !!!!
     */
    public function __construct($callable)
    {
        // TODO : ajouter une vérification si le callable a le bon format ? par exemple si c'est un is_callable ou is_object ou is_string ou is_array (éventeullement vérifier que la tableau a une taille de 2 éléments et que le 1er élément est une string ou un objet et que le 2eme élement est une string) ???? ou alors indiquer qu'une NotCallableException sera levée par le package Invoker lors du call !!!!
        $this->callable = $callable;
    }

    // TODO : indiquer dans la phpDoc tous les typehints possibles pour $callable !!!
    // TODO : indiquer qu'une exception est levée si le container n'est pas défini par la méthode getContainer() !!!
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : reprendre l'exemple de code du CallableHandler pour gérer la résolution du callable.
        $response = $this->getContainer()->injector()->invoke($this->callable, [$request, $handler]);

        if ($response instanceof ResponseInterface) {
            return $response;
        }
        /*
        if ($response instanceof MiddlewareInterface) {
            return $response->process($request, $handler);
        }*/

        // TODO : reprendre l'exemple de code du CallableHandler pour gérer l'exception dans le cas ou la retourn n'est pas une response valide !!!!
        //throw new InvalidMiddlewareDefinitionException($this->callback);
        throw new LogicException(sprintf(
            'Decorated callable middleware of type "%s" failed to produce a response.',
            is_object($this->callable) ? get_class($this->callable) : gettype($this->callable)
        ));
    }
}
