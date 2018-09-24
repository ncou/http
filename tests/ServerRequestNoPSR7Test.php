<?php

declare(strict_types=1);

namespace Tests\Http;

use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use PHPUnit\Framework\TestCase;

class ServerRequestNoPSR7Test extends TestCase
{
    public function testServerRequestIsMethod()
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $this->assertTrue($request->isMethod('GET'));
    }

    public function testServerRequestIsMethodCaseSensitive()
    {
        $request = new ServerRequest('GeT', new Uri('/'));

        $this->assertTrue($request->isMethod('get'));
    }

    public function testServerRequestIsSecure()
    {
        $request = new ServerRequest('GeT', new Uri('httpS://www.foo.bar'));

        $this->assertSame('https', $request->getScheme());
        $this->assertTrue($request->isSecure());
    }

    public function testServerRequestIsNotSecure()
    {
        $request = new ServerRequest('GeT', new Uri('hTTp://www.foo.bar'));

        $this->assertSame('http', $request->getScheme());
        $this->assertFalse($request->isSecure());
    }

    public function testServerRequestHasCookie()
    {
        $_COOKIE['foo'] = 'bar';
        $request = new ServerRequest('GET', new Uri('/'));
        $request = $request->withCookieParams($_COOKIE);

        $this->assertTrue($request->hasCookie('foo'));
    }

    public function testServerRequestGetCookieParam()
    {
        $_COOKIE['foo'] = 'bar';
        $request = new ServerRequest('GET', new Uri('/'));
        $request = $request->withCookieParams($_COOKIE);

        $this->assertSame('bar', $request->getCookieParam('foo'));
        $this->assertSame('bar', $request->getCookieParam('not_exist', 'bar'));
    }
}
