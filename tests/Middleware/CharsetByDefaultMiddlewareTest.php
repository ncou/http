<?php

declare(strict_types=1);

namespace Chiron\Http\Test\Middleware;

use Chiron\Container\Container;
use Chiron\Csrf\Config\CsrfConfig;
use Chiron\Csrf\Exception\TokenMismatchException;
use Chiron\Http\Middleware\RequestIdMiddleware;
use Chiron\Csrf\Middleware\CsrfTokenMiddleware;
use Chiron\Http\Middleware\AllowedHostsMiddleware;
use Chiron\Http\Middleware\CharsetByDefaultMiddleware;
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

use Chiron\Event\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Chiron\Event\ListenerProvider;
use Psr\EventDispatcher\ListenerProviderInterface;

use Psr\Http\Message\ResponseFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

use Chiron\Http\Listener\PipelineListener;

class CharsetByDefaultMiddlewareTest extends TestCase
{
    private $container;

    public function setUp(): void
    {
        $this->container = new Container();

        // TODO : start - attention ce code n'est pas propre il faudrait un provider dans le package chiron/core pour initialiser ce binding !!!!
        $this->container->singleton(EventDispatcher::class);
        $this->container->singleton(EventDispatcherInterface::class, EventDispatcher::class);

        $listener = new ListenerProvider();
        $listener->add(new PipelineListener($this->container));

        $this->container->singleton(ListenerProviderInterface::class, $listener);

        $this->container->singleton(ResponseFactoryInterface::class, Psr17Factory::class);
    }

    public function testWithoutContentTypeHeader(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            return new Response();
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertFalse($response->hasHeader('Content-Type'));
    }

    public function testWithContentTypeHeaderWithoutCharsetButNotTextual(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'foo/bar');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('foo/bar', $response->getHeaderLine('Content-Type'));
    }

    public function testWithContentTypeHeaderWithoutCharsetButTextual(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'text/foobar');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('text/foobar; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }



    public function testWithContentTypeUpperCaseWithoutCharset(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'TEXT/FOOBAR');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('text/foobar; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testWithContentTypeUpperCaseWithCharsetSoUntouched(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'TEXT/FOOBAR; CHARSET=iso-8859-1');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('TEXT/FOOBAR; CHARSET=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }



    public function testWithContentTypeHeaderWithCharsetButNotTextual(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'foo/bar; charset=iso-8859-1');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('foo/bar; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }

    public function testWithContentTypeHeaderWithCharsetButTextual(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'text/foobar; charset=iso-8859-1');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('text/foobar; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }








    public function testWithContentTypeHeaderWithoutCharsetAndBoundaryButNotTextual(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'foo/bar; boundary=something');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('foo/bar; boundary=something', $response->getHeaderLine('Content-Type'));
    }

    public function testWithContentTypeHeaderWithoutCharsetAndBoundaryButTextual(): void
    {
        $handler = static function (ServerRequestInterface $request) {
            $response = new Response();
            $response = $response->withHeader('Content-Type', 'text/foobar; boundary=something');

            return $response;
        };

        $middleware = new CharsetByDefaultMiddleware('utf-8');
        $core = $this->httpCore([$middleware], $handler);

        $response = $this->get($core, '/');

        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertEquals('text/foobar; boundary=something; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }


// TODO : méthodes à mettre dans une classe TestCase dédiée et étendre de cette classe pour nos tests http !!!!
    protected function httpCore(array $middlewares = [], Closure $handler): Http
    {
        $http = $this->container->get(Http::class);

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
