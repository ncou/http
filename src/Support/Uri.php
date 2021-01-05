<?php

declare(strict_types=1);

namespace Chiron\Http\Support;

use Psr\Http\Message\ServerRequestInterface;

// TODO : renommer lac classe en Request et la déplacer dans un package ou un répertoire "support" ???
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

    /**
     * Return 'True' if the host is either an exact match or a match
     * to the wildcard pattern.
     * Any pattern beginning with a period matches a domain and all of its
     * subdomains. (e.g. '.example.com' matches 'example.com' and
     * 'foo.example.com'). Anything else is an exact string match.
     *
     * Note: This function assumes that the given host is lowercased and has
     * already had the port, if any, stripped off.
     */
    // TODO : déplacer cette méthode dans une autre classe (car c'est pas vraiment en relation avec la partie Url de la request !!!) ????
    // TODO : attention, pas sur que cela fonctionne si on passe un patern qui est une chaine vide ''.
    public static function isSameDomain(string $host, string $pattern): bool
    {
        // host is lowercase as per RFC 952/2181, so host patterns should be lowercase too.
        $pattern = strtolower($pattern);

        // TODO : Faire 2 ou 3 "if" distincts ??? pour le cas A) host = pattern, ou le cas ou pattern commence par un '.' B1) et le host se termine par par ce pattern ou B2) et le host est égale au pattern sans le point. ca donne ca :
        // A) $pattern = 'exemple.com' / $host = 'exemple.com'   => true
        // B1) $pattern = '.exemple.com' / $host = 'foo.exemple.com'   => true
        // B2) $pattern = '.exemple.com' / $host = 'exemple.com'   => true
        return static::startswith($pattern, '.') && (static::endswith($host, $pattern) || $host === substr($pattern, 1)) || $host === $pattern;
    }

    /**
     * Starts the $haystack string with the prefix $needle?
     */
    // TODO : créer une classe Str ou Strings dans le package chiron/support pour déplacer et mutualiser cette méthode ????
    public static function startsWith(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }


    /**
     * Ends the $haystack string with the suffix $needle?
     */
    // TODO : créer une classe Str ou Strings dans le package chiron/support pour déplacer et mutualiser cette méthode ????
    public static function endsWith(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}
