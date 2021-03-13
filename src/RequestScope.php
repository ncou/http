<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\Container;
use Chiron\Container\SingletonInterface;
use Chiron\Http\Traits\PipelineTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplPriorityQueue;
use Psr\Container\NotFoundExceptionInterface;
use Chiron\Core\Exception\ScopeException;
use Psr\Container\ContainerInterface;

final class RequestScope
{
    /** @var ContainerInterface */
    private $container = null;

    /**
     * @param ContainerInterface $container
     */
    // TODO : lui ajouter en paramétre une classe ProxiesConfig ou ProxyConfig ce qui permettra de récupérer les adresses IP ou plage d'ip qui sont "trusted" pour récupérer l'adresse ip et host/pot/scheme depuis les headers X-Forwarded-xxx, éventuellement au lieu de créer un fichier de config proxy.php.dist, on pourrait utiliser le fichier http.php.dist pour stocker ces infos, et utiliser un subset dans la classe de config pour récupérer que la partie proxy.
    // TODO : utiliser directement un objet de type "Chiron\Container::class"
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get active instance of ServerRequestInterface and reset all bags if instance changed.
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        // ensure the request instance is fresh (get it from the container).
        $this->refreshRequestInstance();
        //$this->request = get_current_request();

        return $this->request;
    }

    /**
     * Grab the latest Request instance 'existing' in the container.
     *
     * @throws ScopeException In case the Request is not found in the container.
     */
    private function refreshRequestInstance(): void
    {
        try {
            $this->request = $this->container->get(ServerRequestInterface::class); // externaliser cet appel dans une fonction globale du genre get_request() ou current_request()
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException(
                'Unable to get "ServerRequestInterface" in active container scope.',
                $e->getCode(),
                $e
            );
        }
    }
}
