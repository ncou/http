<?php

declare(strict_types=1);

namespace Chiron\Http\Tests\Middleware;

use Chiron\Container\Container;
use Chiron\Csrf\Config\CsrfConfig;
use Chiron\Csrf\Exception\TokenMismatchException;
use Chiron\Http\Middleware\RequestIdMiddleware;
use Chiron\Csrf\Middleware\CsrfTokenMiddleware;
use Chiron\Http\Middleware\AllowedHostsMiddleware;
use Chiron\Http\Http;
use Chiron\Security\Config\SecurityConfig;
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
use Chiron\Core\Config\SettingsConfig;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Exception\DisallowedHostException;

class RequestIdMiddlewareTest extends TestCase
{
    private $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    public function testRequestId(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response->getBody()->write($request->getHeaderLine(RequestIdMiddleware::HEADER_NAME));

            return $response;
        };

        $core = $this->httpCore([RequestIdMiddleware::class], $handler);

        $response = $this->get($core, '/');

        self::assertMatchesRegularExpression(
            '/[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}/',
            $response->getHeaderLine(RequestIdMiddleware::HEADER_NAME)
        );
        self::assertEquals($response->getHeaderLine(RequestIdMiddleware::HEADER_NAME), (string) $response->getBody());
    }


// TODO : méthodes à mettre dans une classe TestCase dédiée et étendre de cette classe pour nos tests http !!!!
    protected function httpCore(array $middlewares = [], Closure $handler): Http
    {
        $http = new Http($this->container);

        foreach ($middlewares as $middleware) {
            $http->addMiddleware($middleware);
        }

        $http->setHandler($handler);

        return $http;
    }

    protected function get(
        Http $core,
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $core->handle($this->request($uri, 'GET', $query, $headers, $cookies));
    }

    protected function request(
        $uri,
        string $method,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequest {
        $request = new ServerRequest($method, $uri, $headers);

        $request = $request->withQueryParams($query)->withCookieParams($cookies);

        return $request;
    }

    /**
     * phpunit 8 support
     */
    public static function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        if (method_exists(parent::class, 'assertMatchesRegularExpression')) {
            parent::assertMatchesRegularExpression($pattern, $string, $message);
            return;
        }

        self::assertRegExp($pattern, $string, $message);
    }
}
