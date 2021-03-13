<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Support\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Traits\ParameterizedTrait;

//https://pypi.org/project/django-request-id/
//https://github.com/nigma/django-request-id/tree/master/request_id

// TODO : permettre aussi au monolog processor de stocker cet id dans les logs :
//https://github.com/php-middleware/request-id/tree/master/src
//https://github.com/php-middleware/request-id/blob/master/src/MonologProcessor.php
//http://www.inanzzz.com/index.php/post/t9az/adding-http-x-request-id-to-symfony-logs
//https://symfony.com/doc/current/logging/processors.html

// TODO : regarder pour conditionner l'ajout du header sur la réponse seulement si c'est défini par l'utilisateur, et possibilité d'utiliser un autre header name
// https://github.com/qandidate-labs/stack-request-id/blob/master/src/Qandidate/Stack/RequestId.php#L58
//https://github.com/yiisoft/yii-web/blob/master/src/Middleware/TagRequest.php

// TODO : renommer la classe en XRequestIdMiddleware ou en UniqueIdMiddleware + passer la constante à X-Unique-ID ???
// TODO : attention la classe Random n'est pas présente dans les dépendances du package !!!!
// TODO : ajouter une méthode __constructor($name = 'X-Request-ID') ce qui permettra lors de l'ajout du middleware de modifier le header name facilement, via un autowire avec passage d'informations au constructeur. Cela va donne un truc du genre :     $http->addMiddleware(RequestIdMiddleware::class, ['name' => 'X-Unique-ID']);
final class RequestIdMiddleware implements ParameterizedMiddlewareInterface
{
    use ParameterizedTrait;

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
        $headerName = $this->getParameter('header_name', self::HEADER_NAME); // TODO : ajouter plutot un constructeur et initialiser la valeur par défaut de header_name en faisant un setParameters(['header_name' => self::HEADER_NAME]); et passer la variable de classe HEADER_NAME en private !!!!
        $id = $request->getHeader($headerName);

        // generate an unique identifier if not already present.
        // TODO : il faudrait plutot faire un if (! $request->hasHeader(xxx)) plutot que le test avec empty
        // TODO : le $id sera un tableau vide si il n'existe pas.
        if (empty($id)) {
            $id = Random::uuid();
            $request = $request->withHeader($headerName, $id);
        }

        $response = $handler->handle($request); // TODO : il faudrait aussi ajouter cet identifiant dans les attributs de la request comme ca on pourrait l'utiliser plus tard !!!!

        // persist the unique id in the response header list.
        return $response->withHeader($headerName, $id);
    }
}
