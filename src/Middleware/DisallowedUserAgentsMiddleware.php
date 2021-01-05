<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Support\Uri;
use Chiron\Config\SettingsConfig;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Exception\PermissionDeniedException;

/**
 * Protection against unwanted user-agents (ex: bad bot/crawler).
 */
final class DisallowedUserAgentsMiddleware implements MiddlewareInterface
{
	/** @var array */
	private $disallowedUserAgents;

    /**
     * @param HttpConfig $httpConfig
     * @param SettingsConfig   $settingsConfig
     */
    public function __construct(HttpConfig $httpConfig)
    {
        $this->disallowedUserAgents = $httpConfig->getDisallowedUserAgents();
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
        // Check the 'User-Agent' header against with the banlist.
        if (! $this->isAllowedUserAgent($request)) {
            // Represents an http 403 error code (forbidden access).
        	throw new PermissionDeniedException('Forbidden user agent');
        }

        return $handler->handle($request);
    }

    /**
     * Validate the user-agent value against a banlist.
     *
     * Check that the user-agent matches a pattern in the given list of 'disallowed_user_agents'.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool 'True' for an allowed user-agent, 'False' otherwise.
     */
    // TODO : exemple : https://github.com/ncou/Chiron-Middlewares/blob/master/src/Chiron/Http/Middleware/UserAgentBlockerMiddleware.php#L64
    private function isAllowedUserAgent(ServerRequestInterface $request): bool
    {
        $userAgent = $request->getHeaderLine('User-Agent');

        // TODO : remplacer le wildcard '*' par son expression regex => https://github.com/illuminate/validation/blob/d5819507c22e988a8d62266274f6905ba1faf3e0/ValidationRuleParser.php#L127 /  https://github.com/illuminate/validation/blob/d5819507c22e988a8d62266274f6905ba1faf3e0/ValidationData.php#L58  / https://github.com/illuminate/validation/blob/735565f9431f517ed7ede54cdbe861ff6e7b4289/Concerns/FormatsMessages.php#L102    /    https://github.com/illuminate/validation/blob/735565f9431f517ed7ede54cdbe861ff6e7b4289/Validator.php#L580
        // TODO : il faudra utiliser un flag /u pour la regex pour gérer les chaines unicodes dans les urls !!!!
        foreach ($this->disallowedUserAgents as $pattern) {
            // TODO : ne pas utiliser le '@' pour silenced les erreurs, mais il faut plutot utiliser un wrapper autour de la méthode pour ensuite vérifier le preg_last_error() et faire cette vérification dans un Bootloader pour lever une exception BootException si on trouve une regex invalide dans le fichier de configuration. Exemple => https://github.com/symfony/symfony/blob/4a053e5fed4422a834596da6a643978b603db18e/src/Symfony/Component/Yaml/Parser.php#L1089
            if (@preg_match($pattern, $userAgent)) {
                // TODO : utiliser cette méthode pour appeller de maniére safe une regex qui risque de lever une exception => https://github.com/nette/utils/blob/master/src/Utils/Strings.php#L535
                return false;
            }
        }

        return true;
    }
}
