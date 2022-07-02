<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Message\RequestMethod as Method;
use Chiron\Http\Support\Uri;
use Chiron\Http\Exception\Client\NotFoundHttpException;
use Psr\Http\Message\ResponseFactoryInterface;
use Chiron\Routing\Map;
use Chiron\Http\ErrorHandler\Renderer\HtmlRenderer;

final class NotFoundDebugMiddleware implements MiddlewareInterface
{
    /** @param Map $map */
    private $map;
    /** @param HtmlRenderer $htmlRenderer */
    private $renderer;

    public function __construct(Map $map, HtmlRenderer $renderer)
    {
        $this->map = $map;
        $this->renderer = $renderer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch(NotFoundHttpException $e){
            // TODO : faire ce bout de code uniquement si on est en mode APP_DEBUG === true, sinon on fait un throw $e pour laisser l'exception se propager !!!! ou alors vérifier dans le bootloader que ce middleware est ajouté uniquement si le mode debug est activé.
            $response = $this->displayRouteNotFoundDetails($request);
        }

        return $response;
    }

    private function displayRouteNotFoundDetails(ServerRequestInterface $request): ResponseInterface
    {
        if($this->map->isEmpty()) {
          $path = dirname(__DIR__). '/../resources/default_urlconf.html';
          $data = ['version' => '1.0']; // TODO : utiliser le numéro de version présent dans la classe Framework::class
        } else {
          $path = dirname(__DIR__). '/../resources/technical_404.html';
          $data = [
            'request_absolute_uri' => (string) $request->getUri(),
            'request_path' => $request->getUri()->getPath(),
            'request_method' => $request->getMethod(),
          ];

          $data['patterns'] = $this->wrapHtmlListRoutes($this->map->getRoutes());
        }

        $response = $this->renderer->render($path, $data)->withStatus(404); // TODO : utiliser une classe de constantes pour les codes HTTP !!!!

        return $response;
    }

    private function wrapHtmlListRoutes(array $routes): string
    {
        $patterns = '<ol>';

        foreach ($routes as $route) {
            $patterns .= '<li>' . $route->getPath() . '</li>';
          }

        $patterns .= '</ol>';

        return $patterns;
    }
}
