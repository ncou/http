<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Server\MiddlewareInterface;

interface ParameterizedMiddlewareInterface extends MiddlewareInterface
{
    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void;
}
