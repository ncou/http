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
        $host = $request->getUri()->getHost();
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
}
