<?php

declare(strict_types=1);

namespace Chiron\Http\Support;

use Psr\Http\Message\ServerRequestInterface;

final class Uri
{
    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public static function getSchemeAndHttpHost(): string
    {
        return static::getScheme() . '://' . static::getHttpHost();
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public static function getHttpHost(ServerRequestInterface $request): string
    {
        $scheme = static::getScheme($request);
        $port = static::getPort($request);
        $host = static::getHost($request);

        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            return $host;
        }

        return $host . ':' . $port;
    }

    /**
     * Returns the port (even the standard one) on which the request is made.
     *
     * @param ServerRequestInterface $request
     *
     * @return int
     */
    public static function getPort(ServerRequestInterface $request): int
    {
        // Psr7 getPort() method will return null if the port is standard.
        $port = $request->getUri()->getPort();

        return $port ?? static::isSecure($request) ? 443 : 80;
    }

    /**
     * Checks whether the request is secure or not.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public static function isSecure(ServerRequestInterface $request): bool
    {
        return static::getScheme($request) === 'https';
    }

    /**
     * Returns the HTTP scheme being requested.
     *
     * If no scheme is present, this method return an empty string.
     * The scheme is already normalized to lowercase, as per RFC 3986 Section 3.1.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public static function getScheme(ServerRequestInterface $request): string
    {
        return $request->getUri()->getScheme();
    }

    /**
     * Returns the HTTP host being requested.
     *
     * If no host is present, this method return an empty string.
     * The host is already normalized to lowercase, as per RFC 3986 Section 3.1.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public static function getHost(ServerRequestInterface $request): string
    {
        return $request->getUri()->getHost();
    }
}
