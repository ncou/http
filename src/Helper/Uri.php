<?php

declare(strict_types=1);

namespace Chiron\Http\Helper;

use Psr\Http\Message\ServerRequestInterface;
use Chiron\Support\Str;

final class Uri
{
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
    // TODO : renommer le paramétre $host en $hostname
    // TODO : méthode à déplacer dans la classe AllowedHostsMiddleware ???
    public static function isSameDomain(string $host, string $pattern): bool
    {
        // host is lowercase as per RFC 952/2181, so host patterns should be lowercase too.
        $pattern = strtolower($pattern); // TODO : forcer aussi le host à être en lowercase, juste au cas ou !!!!

        // TODO : Faire 2 ou 3 "if" distincts ??? pour le cas A) host = pattern, ou le cas ou pattern commence par un '.' B1) et le host se termine par par ce pattern ou B2) et le host est égale au pattern sans le point. ca donne ca :
        // A) $pattern = 'exemple.com' / $host = 'exemple.com'   => true
        // B1) $pattern = '.exemple.com' / $host = 'foo.exemple.com'   => true
        // B2) $pattern = '.exemple.com' / $host = 'exemple.com'   => true
        return Str::startswith($pattern, '.') && (Str::endswith($host, $pattern) || $host === substr($pattern, 1)) || $host === $pattern;
    }
}
