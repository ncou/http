<?php

declare(strict_types=1);

namespace Chiron\Http\Test\Listener;

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

use Chiron\Http\Test\Fixtures\CallableMiddleware;
use Chiron\Http\Test\Fixtures\CallableRequestHandler;

use Chiron\Event\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Chiron\Event\ListenerProvider;
use Psr\EventDispatcher\ListenerProviderInterface;

use Chiron\Http\Bootloader\HttpListenerBootloader;
use Chiron\Http\CallableHandler;

use Chiron\Http\Listener\PipelineListener;

// TODO : faire en sorte de ne plus avoir besoin des classes Fixtures\CallableMiddleware et Fixtures\CallableRequestHandler, essayer d'utiliser directement le Http\CallableHandler

class PipelineListenerTest extends TestCase
{
    public function testBindLatestRequestInContainer()
    {
        $container = $this->makeContainer();

        $middleware_1 = new CallableMiddleware(function ($request, $handler) use ($container) {
            $this->assertSame($request, $container->get(ServerRequestInterface::class));
            $request = $request->withAttribute('foo', true);

            return $handler->handle($request);
        });

        $middleware_2 = new CallableMiddleware(function ($request, $handler) use ($container) {
            $this->assertSame($request, $container->get(ServerRequestInterface::class));
            $request = $request->withAttribute('bar', true);

            return $handler->handle($request);
        });

        $fallback = new CallableRequestHandler(function ($request) use ($container) {
            $this->assertSame($request, $container->get(ServerRequestInterface::class));
            $this->assertTrue($container->get(ServerRequestInterface::class)->getAttribute('foo'));
            $this->assertTrue($container->get(ServerRequestInterface::class)->getAttribute('bar'));

            return new Response();
        });

        $http = $container->get(Http::class);
        $http->addMiddleware($middleware_1);
        $http->addMiddleware($middleware_2);
        $http->setHandler($fallback);

        $this->assertFalse($container->has(ServerRequestInterface::class));

        $response = $http->handle(new ServerRequest('GET', 'http://foo.bar'));

        $this->assertTrue($container->has(ServerRequestInterface::class));
        $this->assertTrue($container->get(ServerRequestInterface::class)->getAttribute('foo'));
        $this->assertTrue($container->get(ServerRequestInterface::class)->getAttribute('bar'));
    }

    public function testBindLatestRequestInContainerWithOnlyAFallbackHandler()
    {
        $fallback = new CallableHandler(function (ServerRequestInterface $request) {
            $this->assertInstanceOf(ServerRequestInterface::class, $request);

            return new Response();
        });

        $container = $this->makeContainer();
        $http = $container->get(Http::class);
        $http->setHandler($fallback);

        $response = $http->handle(new ServerRequest('GET', 'http://foo.bar'));

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    private function makeContainer(): Container
    {
        $container = new Container();

        // TODO : start - attention ce code n'est pas propre il faudrait un provider dans le package chiron/core pour initialiser ce binding !!!!
        $container->singleton(EventDispatcher::class);
        $container->singleton(EventDispatcherInterface::class, EventDispatcher::class);

        $listener = new ListenerProvider();
        $listener->add(new PipelineListener($container));

        $container->singleton(ListenerProviderInterface::class, $listener);
        // TODO : end

        return $container;
    }
}
