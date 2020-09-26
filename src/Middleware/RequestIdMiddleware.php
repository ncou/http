<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Core\Helper\Random;

// TODO : regarder pour conditionner l'ajout du header sur la réponse seulement si c'est défini par l'utilisateur, et possibilité d'utiliser un autre header name
// https://github.com/qandidate-labs/stack-request-id/blob/master/src/Qandidate/Stack/RequestId.php#L58
//https://github.com/yiisoft/yii-web/blob/master/src/Middleware/TagRequest.php

// TODO : renommer la classe en XRequestIdMiddleware ou en UniqueIdMiddleware + passer la constante à X-Unique-ID ???
final class RequestIdMiddleware implements MiddlewareInterface
{
    // TODO : permettre à l'utilisateur de configurer la valeur de cette clés ???? avec une méthode setHeaderName() par exemple
    public const HEADER_NAME = 'X-Request-ID'; // 'X-Correlation-ID' // 'X-Unique-ID'

    /**
     * Add a unique identifier for each HTTP request.
     *
     * @param ServerRequestInterface  $request request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $id = $request->getHeader(self::HEADER_NAME);

        // generate an unique identifier if not already present.
        // TODO : il faudrait plutot faire un if (! $request->hasHeader(xxx)) plutot que le test avec empty
        if (empty($id)) {
            $id = Random::generateId();
            $request = $request->withHeader(self::HEADER_NAME, $id);
        }

        $response = $handler->handle($request);

        // persist the unique id in the response header list.
        return $response->withHeader(self::HEADER_NAME, $id);
    }
}
