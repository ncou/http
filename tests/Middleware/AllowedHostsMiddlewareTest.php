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
use Chiron\Core\Core;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Exception\DisallowedHostException;

use Chiron\Event\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Chiron\Event\ListenerProvider;
use Psr\EventDispatcher\ListenerProviderInterface;
use Chiron\Http\Listener\PipelineListener;

use Psr\Http\Message\ResponseFactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

//https://github.com/django/django/blob/5fcfe5361e5b8c9738b1ee4c1e9a6f293a7dda40/tests/requests/tests.py

//https://github.com/symfony/symfony/blob/4a2e4e890c54f8cd025b8a13ba9529204b21d3c6/src/Symfony/Component/HttpFoundation/Tests/RequestTest.php#L2114
//https://github.com/symfony/symfony/blob/4a2e4e890c54f8cd025b8a13ba9529204b21d3c6/src/Symfony/Component/HttpFoundation/Tests/RequestTest.php#L2051

//https://github.com/symfony/symfony/blob/4a2e4e890c54f8cd025b8a13ba9529204b21d3c6/src/Symfony/Component/HttpFoundation/Tests/RequestTest.php#L2127

// TODO : ajouter des tests sur la méthode pour splitter le host et le port avec l'exemple suivant : https://github.com/symfony/symfony/blob/4a2e4e890c54f8cd025b8a13ba9529204b21d3c6/src/Symfony/Component/HttpFoundation/Tests/RequestTest.php#L2145   + ces 2 url qui doivent échouer à la validation du host : 'example.com:80/badpath' et  'example.com: recovermypassword.com'
// Plus faire le test lorsqu'il y a un point à la fin du host !!!! => https://github.com/django/django/blob/5fcfe5361e5b8c9738b1ee4c1e9a6f293a7dda40/tests/requests/tests.py#L813

class AllowedHostsMiddlewareTest extends TestCase
{
    private $container;
    private $signer;

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

    public function testAllowedHostsWildcard(): void
    {
        $handler = static function (ServerRequestInterface $r) {
                return new Response();
        };

        $this->setDebug(false);
        $this->setAllowedHosts('*');

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $response = $this->get($core, 'http://foobar.com');
        self::assertSame(200, $response->getStatusCode());
    }

    public function testEmptyHost(): void
    {
        $handler = static function (ServerRequestInterface $r) {
                return new Response();
        };

        $this->setDebug(true);
        $this->setAllowedHosts([]);

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $this->expectException(DisallowedHostException::class);
        $this->expectExceptionMessage('Untrusted Host header "". The domain name provided is not valid according to RFC 1034/1035.');

        $response = $this->get($core, '/');
    }

    /**
     * @dataProvider localHosts
     */
    public function testAllowedHostsLocalHostDuringDebug($host): void
    {
        $handler = static function (ServerRequestInterface $r) {
                return new Response();
        };

        $this->setDebug(true);
        $this->setAllowedHosts([]);

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $response = $this->get($core, 'http://' . $host);
        self::assertSame(200, $response->getStatusCode());
    }

    public function localHosts(): array
    {
        return [
            ['localhost'],
            ['localhost.'], // Trailing dot is for 'Absolute domain name'
            ['localhost:8080'],
            ['subdomain.localhost'],
            ['subdomain.localhost.'], // Trailing dot is for 'Absolute domain name'
            ['127.0.0.1'],
            ['127.0.0.1:80'],
            ['[::1]'],
        ];
    }

    /**
     * @dataProvider legitHosts
     */
    public function testAllowedHosts($host): void
    {
        $handler = static function (ServerRequestInterface $r) {
                return new Response();
        };

        $this->setDebug(false);
        $this->setAllowedHosts(
            [
                'example.com',
                '12.34.56.78',
                '[2001:19f0:feee::dead:beef:cafe]',
                'xn--4ca9at.com',
                '.multitenant.com',
                'INSENSITIVE.com',
                '[::ffff:169.254.169.254]'
            ]
        );

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $response = $this->get($core, 'http://' . $host);
        self::assertSame(200, $response->getStatusCode());
    }

    public function legitHosts(): array
    {
        return [
            ['example.com'],
            ['example.com.'], // Trailing dot is for 'Absolute domain name'
            ['example.com:80'],
            ['12.34.56.78'],
            ['12.34.56.78:443'],
            ['[2001:19f0:feee::dead:beef:cafe]'],
            ['[2001:19f0:feee::dead:beef:cafe]:8080'],
            ['xn--4ca9at.com'], # Punycode for öäü.com
            ['anything.multitenant.com'],
            ['multitenant.com'],
            ['insensitive.com'],
            ['[::ffff:169.254.169.254]'],
        ];
    }

    /**
     * @dataProvider poisonedHosts
     */
    public function testDisallowedHosts($host): void
    {
        $handler = static function (ServerRequestInterface $r) {
                return new Response();
        };

        $this->setDebug(false);
        $this->setAllowedHosts(
            [
                'example.com'
            ]
        );

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $this->expectException(DisallowedHostException::class);
        $this->expectExceptionMessage('Untrusted Host header');

        $response = $this->get($core, 'http://' . $host);
    }

    public function poisonedHosts(): array
    {
        return [
            ['example.com@evil.tld'],
            ['example.com:dr.frankenstein@evil.tld'],
            ['example.com:dr.frankenstein@evil.tld:80'],
            ['example.com..'],
            ['other.com'],
        ];
    }

    private function setDebug(bool $debug): void
    {
        $core = new Core($debug);
        $this->container->bind(Core::class, $core);
    }

    /**
     * @param string|array $allowedHosts
     */
    private function setAllowedHosts($allowedHosts): void
    {
        $httpConfig = new HttpConfig([
            'allowed_hosts' => (array) $allowedHosts,
        ]);
        $this->container->bind(HttpConfig::class, $httpConfig);
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

    protected function post(
        Http $core,
        $uri,
        array $data = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $core->handle($this->request($uri, 'POST', [], $headers, $cookies)->withParsedBody($data));
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

    protected function fetchCookies(ResponseInterface $response): array
    {
        $result = [];

        foreach ($response->getHeaders() as $header) {
            foreach ($header as $headerLine) {
                $chunk = explode(';', $headerLine);
                if (! count($chunk) || mb_strpos($chunk[0], '=') === false) {
                    continue;
                }

                $cookie = explode('=', $chunk[0]);
                $result[$cookie[0]] = rawurldecode($cookie[1]);
            }
        }

        return $result;
    }
}
