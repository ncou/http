<?php

declare(strict_types=1);

namespace Chiron\Http\Traits;

use Chiron\Http\CallableHandler;
use Chiron\Http\MiddlewareBinding;
use Chiron\Pipeline\Pipeline;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : créer plutot une classe abstraite avec ce bout de code + un implement ParameterizedMiddlewareInterface, ca évitera de garder un trait pas forcement trés utile !!!

/**
 * Trait used in the 'parameterized' Middlewares classes.
 */
trait ParameterizedTrait
{
    /** @var array The middleware parameters */
    private $parameters = [];

    /**
     * Creates middleware binding (class+parameters) to be used by this middleware
     *
     * @param array $parameters The parameters to include in this middleware
     *
     * @return MiddlewareBinding The middleware binding (class+parameters)
     */
    // TODO : éventuellement déplacer cette méthode dans un autre trait pour rendre le ParameterizedTrait plus générique dans le cas ou on veut l'utiliser dans une classe autre qu'un middleware.
    public static function with(array $parameters) : MiddlewareBinding
    {
        return new MiddlewareBinding(static::class, $parameters);
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Gets the value of a parameter
     *
     * @param string $name The name of the parameter to get
     * @param mixed $default The default value
     * @return mixed|null The parameter's value if it is set, otherwise null
     */
    protected function getParameter(string $name, $default = null)
    {
        if (! array_key_exists($name, $this->parameters)) {
            return $default;
        }

        return $this->parameters[$name];
    }
}
