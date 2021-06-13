<?php

declare(strict_types=1);

namespace Chiron\Http\Test\Middleware;

use Chiron\Container\Container;
use Chiron\Csrf\Config\CsrfConfig;
use Chiron\Csrf\Exception\TokenMismatchException;
use Chiron\Csrf\Middleware\CsrfProtectionMiddleware;
use Chiron\Csrf\Middleware\CsrfTokenMiddleware;
use Chiron\Http\Middleware\AllowedHostsMiddleware;
use Chiron\Http\Http;
use Chiron\Security\Security;
use Chiron\Security\Signer;
use Chiron\Support\Random;
use Closure;
use LogicException;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Exception\DisallowedHostException;
use Chiron\Http\Helper\Uri;

class UriTest extends TestCase
{
    /**
     * @dataProvider sameDomains
     */
    public function testUriGood(string $host, string $pattern): void
    {
        self::assertTrue(Uri::isSameDomain($host, $pattern));
    }

    /**
     * @dataProvider notSameDomains
     */
    public function testUriBad(string $host, string $pattern): void
    {
        self::assertFalse(Uri::isSameDomain($host, $pattern));
    }

    public function sameDomains(): array
    {
        return [
            ['example.com', 'example.com'],
            ['example.com', '.example.com'],
            ['foo.example.com', '.example.com'],
            ['example.com:8888', 'example.com:8888'],
            ['example.com:8888', '.example.com:8888'],
            ['foo.example.com:8888', '.example.com:8888'],
        ];
    }

    public function notSameDomains(): array
    {
        return [
            ['example2.com', 'example.com'],
            ['foo.example.com', 'example.com'],
            ['example.com:9999', 'example.com:8888'],
            ['foo.example.com:8888', ''],
        ];
    }
}
