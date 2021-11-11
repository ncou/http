<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

//https://github.com/symfony/http-kernel/blob/409eba7fa9eccaeb419bd2f35edc9c81fb56323f/Exception/ControllerDoesNotReturnResponseException.php
//https://github.com/symfony/http-kernel/blob/0996d531074e0fb3f60b2af0a0d758c03fc47396/Tests/HttpKernelTest.php#L210

/**
 * Exception thrown when the controller doesn't return a Psr7 Response.
 */
final class MissingResponseException extends \LogicException
{
    public function __construct(string $message, callable $controller, string $file, int $line)
    {
        parent::__construct($message);

        if (!$controllerDefinition = $this->parseControllerDefinition($controller)) {
            return;
        }

        $this->file = $controllerDefinition['file'];
        $this->line = $controllerDefinition['line'];
        $r = new \ReflectionProperty(\Exception::class, 'trace');
        $r->setAccessible(true);
        $r->setValue($this, array_merge([
            [
                'line' => $line,
                'file' => $file,
            ],
        ], $this->getTrace()));
    }

    private function parseControllerDefinition(callable $controller): ?array
    {
        if (\is_string($controller) && str_contains($controller, '::')) {
            $controller = explode('::', $controller);
        }

        if (\is_array($controller)) {
            try {
                $r = new \ReflectionMethod($controller[0], $controller[1]);

                return [
                    'file' => $r->getFileName(),
                    'line' => $r->getEndLine(),
                ];
            } catch (\ReflectionException $e) {
                return null;
            }
        }

        if ($controller instanceof \Closure) {
            $r = new \ReflectionFunction($controller);

            return [
                'file' => $r->getFileName(),
                'line' => $r->getEndLine(),
            ];
        }

        if (\is_object($controller)) {
            $r = new \ReflectionClass($controller);

            try {
                $line = $r->getMethod('__invoke')->getEndLine();
            } catch (\ReflectionException $e) {
                $line = $r->getEndLine();
            }

            return [
                'file' => $r->getFileName(),
                'line' => $line,
            ];
        }

        return null;
    }
}
