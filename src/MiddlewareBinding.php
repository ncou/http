<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Support\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Middleware\ParameterizedMiddlewareInterface;
use InvalidArgumentException;
use Chiron\Container\ContainerAwareInterface;
use Chiron\Container\ContainerAwareTrait;

// TODO : utiliser un trait pour manipuler les parameters, par exemple : https://github.com/windwalker-io/utilities/blob/master/src/Options/OptionAccessTrait.php

/**
 * Defines a parameterized middleware binding (store the middleware name + the parameters to inject after initialisation)
 */
// TODO : classe à renommer en ParameterizedMiddleware !!!! et à déplacer dans le répertoire Chiron\Http\Middleware
final class MiddlewareBinding implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

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
        // Throw an exception if the middleware is not 'parameterizable' !
        $this->assertParameterizedMiddleware($className);

        $this->className = $className;
        $this->parameters = $parameters;
    }

    /**
     * Throw an exception if the middleware doesn't implement the parameterized interface.
     *
     * @param string $middleware The Middleware class name to check.
     */
    private function assertParameterizedMiddleware(string $middleware): void
    {
        // TODO : lever plutot une ImproperlyConfiguredException::class ????
        if (! is_subclass_of($middleware, ParameterizedMiddlewareInterface::class)) {
                throw new InvalidArgumentException(sprintf(
                'Middleware "%s" should implement interface "%s".',
                $middleware,
                ParameterizedMiddlewareInterface::class
            ));
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Resolve the middleware class name & inject parameters.
        $middleware = $this->container->injector()->build($this->className); // TODO : il faut surement gerer les erreurs et faire un catch du injectorException::class
        $middleware->setParameters($this->parameters);

        return $middleware->process($request, $handler);
    }
}
