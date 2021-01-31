<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Support\Uri;
use Chiron\Core\Config\SettingsConfig;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Exception\DisallowedHostException;

/**
 * Allowed Hosts verification.
 */
final class AllowedHostsMiddleware implements MiddlewareInterface
{
	/** @var array */
	private $allowedHosts;

    /**
     * @param HttpConfig $httpConfig
     * @param SettingsConfig   $settingsConfig
     */
    public function __construct(HttpConfig $httpConfig, SettingsConfig $settingsConfig)
    {
        $this->allowedHosts = $httpConfig->getAllowedHosts();
        // Allow variants of localhost if ALLOWED_HOSTS list is empty and DEBUG is enabled.
        if ($settingsConfig->isDebug() && $this->allowedHosts === []) {
            // localhost and subdomain / IPv4 / IPv6 (brackets for URI use)
            $this->allowedHosts = ['.localhost', '127.0.0.1', '[::1]'];
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface   $handler
     *
     * @throws DisallowedHostException In case the Host header is not present in the whitelist.
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO : attention quand il y a un port dans le host cela ne fonctionne plus !!! il faut aussi faire une exception dédiée dans le cas ou le host est invalid :
        // https://github.com/django/django/blob/a948d9df394aafded78d72b1daa785a0abfeab48/django/http/request.py#L135
        // https://github.com/django/django/blob/a948d9df394aafded78d72b1daa785a0abfeab48/django/http/request.py#L143

        // SPLIT METHOD : https://github.com/django/django/blob/a948d9df394aafded78d72b1daa785a0abfeab48/django/http/request.py#L640
        // TESTS : https://github.com/django/django/blob/a948d9df394aafded78d72b1daa785a0abfeab48/tests/requests/tests.py#L814
        [$host, $port] = $this->splitDomainPort($request->getUri()->getHost());

        // Check the 'Host' header value with the whitelist.
        if (! $this->isValidHost($host)) {
            // Represents an http 412 error code (request precondition failed).
        	throw new DisallowedHostException($host);
        }

        return $handler->handle($request);
    }

    /**
     * Validate the given host for this site.
     *
     * Check that the host matches a host or host pattern in the given list of 'allowed_hosts'.
     *
     * - Any pattern beginning with a period matches a domain and all its subdomains
     * (e.g. '.example.com' matches 'example.com' and any subdomain),
     * - the pattern '*' matches anything,
     * - and anything else must match exactly.
     *
     * @param string $host
     *
     * @return bool 'True' for a valid host, 'False' otherwise.
     */
    private function isValidHost(string $host): bool
    {
        // TODO : gérer les domains unicode => https://github.com/ncou/Chiron-Middlewares/blob/master/src/Chiron/Http/Middleware/ReferralSpamMiddleware.php#L72
    	foreach ($this->allowedHosts as $pattern) {
    		if ($pattern === '*' || Uri::isSameDomain($host, $pattern)) {
    			return true;
    		}
    	}

    	return false;
    }

    /**
     * Return a (domain, port) tuple from a given host.
     * Returned domain is lowercased. If the host is invalid, the domain will be
     * empty.
     */
    // TODO : autre exemple :    https://github.com/symfony/http-client/blob/b458d19fe834f055f4d50a9d0c85633a94023492/NativeHttpClient.php#L285
    // TODO : améliorer le code, on a juste besoin du hostname et pas du port lors de l'utilisation de cette méthode, donc faire simplement une méthode getHostname() qui s'occupe de retourner uniquement le hostname on ne traitera pas la récupération du port. Attention au cas ou le hostname fini par un '.'
    //https://serverfault.com/questions/803033/should-i-append-a-dot-at-the-end-of-my-dns-urls#:~:text=The%20dot%20at%20the%20end,a%20dot%20at%20the%20end.&text=This%20was%20documented%20in%20the,which%20ends%20in%20a%20dot.
    //http://www.dns-sd.org/trailingdotsindomainnames.html
    private function splitDomainPort(string $host): array
    {
        $host = strtolower($host);

        if (! preg_match('/^([a-z0-9.-]+|\[[a-f0-9]*:[a-f0-9\.:]+\])(:\d+)?$/', $host)) {
            return ['', ''];
        }

        // It's an IPv6 address without a port.
        if (substr($host, -1) === ']') {
            return [$host, ''];
        }

        $parts = explode(':', $host);
        if (count($parts) === 2) {
            $domain = $parts[0];
            $port = $parts[1];
        } else {
            $domain = $parts[0];
            $port = '';
        }

        // Remove a trailing dot (if present) from the domain.
        if (substr($domain, -1) === '.') {
            $domain = substr($domain, 0, -1);
        }

        return [$domain, $port];
    }
}
