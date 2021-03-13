<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Support\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Traits\ParameterizedTrait;

/**
 * Attach a unique identifier tag in the request attributes.
 * Note : This value could be usefull later in the logger, or in a view.
 *
 * @param ServerRequestInterface  $request request
 * @param RequestHandlerInterface $handler
 *
 * @return object ResponseInterface
 */
final class TagRequestMiddleware implements MiddlewareInterface
{
    /**
     * Add a unique identifier tag in the request attributes.
     *
     * @param ServerRequestInterface  $request request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tag = Random::uuid();
        $request = $request->withAttribute('request_id', $tag); // TODO : utiliser une constante privée de classe ???

        return $handler->handle($request);
    }
}
