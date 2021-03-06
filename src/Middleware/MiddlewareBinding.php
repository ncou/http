<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Support\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;

/**
 * Defines a parameterized middleware binding (store the middleware name + the parameters to inject after initialisation)
 */
final class MiddlewareBinding
{
    /** @var string The middleware class name */
    private $className;
    /** @var array The middleware parameters */
    private $parameters;

    /**
     * @param string $className The middleware class name
     * @param array $parameters The parameters bound to the middleware
     */
    public function __construct(string $className, array $parameters = [])
    {
        // Throw an exception if the middleware is not 'parameterized' !
        $this->assertParameterizedMiddleware($className);

        $this->className = $className;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Throw an exception if the middleware doesn't implement the parameterized interface.
     *
     * @param string $middleware The Middleware class name to check.
     */
    private function assertParameterizedMiddleware(string $middleware): void
    {
        if (! is_subclass_of($middleware, ParameterizedMiddlewareInterface::class)) {
                throw new InvalidArgumentException(sprintf(
                'Middleware "%s" should implement interface "%s".',
                $middleware,
                ParameterizedMiddlewareInterface::class
            ));
        }
    }
}
