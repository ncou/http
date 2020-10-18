<?php

declare(strict_types=1);

namespace Chiron\Http\Facade;

use Chiron\Core\Facade\AbstractFacade;

// TODO : déplacer cette facade dans le package chiron/http !!!!
// TODO : créer une facade "Request" qui se charge de retourner l'instance de RequestContexte->getRequest() ??? ca serait un bon helper, non ????

final class RequestContext extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return \Chiron\Http\Request\RequestContext::class;
    }
}
