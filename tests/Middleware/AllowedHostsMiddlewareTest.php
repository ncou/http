<?php

declare(strict_types=1);

namespace Chiron\Http\Tests\Middleware;

use Chiron\Container\Container;
use Chiron\Csrf\Config\CsrfConfig;
use Chiron\Csrf\Exception\TokenMismatchException;
use Chiron\Csrf\Middleware\CsrfProtectionMiddleware;
use Chiron\Csrf\Middleware\CsrfTokenMiddleware;
use Chiron\Http\Middleware\AllowedHostsMiddleware;
use Chiron\Http\Http;
use Chiron\Security\Config\SecurityConfig;
use Chiron\Security\Security;
use Chiron\Security\Signer;
use Chiron\Security\Support\Random;
use Closure;
use LogicException;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Config\SettingsConfig;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Exception\DisallowedHostException;

//https://github.com/django/django/blob/5fcfe5361e5b8c9738b1ee4c1e9a6f293a7dda40/tests/requests/tests.py

class AllowedHostsMiddlewareTest extends TestCase
{
    private $container;
    private $signer;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->setAsGlobal(); // TODO : à virer car par défaut le container est maintenant setté as global
    }

    public function testAllowedHostsWildcard(): void
    {
        $handler = static function (ServerRequestInterface $r) {
                return new Response();
        };

        $this->setDebugConfig(false);
        $this->setAllowedHosts('*');

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $response = $this->get($core, 'http://foobar.com');
        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @dataProvider localHosts
     */
    public function testAllowedHostsLocalHostDuringDebug($host): void
    {
        $handler = static function (ServerRequestInterface $r) {
                return new Response();
        };

        $this->setDebugConfig(true);
        $this->setAllowedHosts([]);

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $response = $this->get($core, 'http://' . $host);
        self::assertSame(200, $response->getStatusCode());
    }

    public function localHosts(): array
    {
        return [
            ['localhost'],
            ['localhost:8080'],
            ['subdomain.localhost'],
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

        $this->setDebugConfig(false);
        $this->setAllowedHosts(
            [
                'forward.com',
                'example.com',
                'internal.com',
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

        $this->setDebugConfig(false);
        $this->setAllowedHosts(
            [
                'forward.com',
                'example.com',
                'internal.com',
                '12.34.56.78',
                '[2001:19f0:feee::dead:beef:cafe]',
                'xn--4ca9at.com',
                '.multitenant.com',
                'INSENSITIVE.com',
                '[::ffff:169.254.169.254]'
            ]
        );

        $core = $this->httpCore([AllowedHostsMiddleware::class], $handler);

        $this->expectException(DisallowedHostException::class);
        $this->expectExceptionMessage('Invalid Host header');

        $response = $this->get($core, 'http://' . $host);
        self::assertSame(200, $response->getStatusCode());
    }

    public function poisonedHosts(): array
    {
        return [
            ['example.com@evil.tld'],
            ['example.com:dr.frankenstein@evil.tld'],
            ['example.com:dr.frankenstein@evil.tld:80'],
            ['example.com.:80/badpath'],
            ['example.com.recovermypassword.com'],
        ];
    }

    private function setDebugConfig(bool $debug): void
    {
        $settingsConfig = new SettingsConfig([
            'debug' => $debug,
        ]);
        $this->container->bind(SettingsConfig::class, $settingsConfig);
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
