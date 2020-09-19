<?php

declare(strict_types=1);

namespace Chiron\Http;

use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Container\Container;

final class SapiServerRequestCreator
{
    public static function fromGlobals(): ServerRequestInterface
    {
        // TODO : nettoyer le code, eventuellement faire une fonction globale make() qui crée automatiquement les classes dont le nom est passée en paramétre.
        // TODO : eventuellement faire en sorte que si on appel juste container() sans paramétre cela retourne l'instance courrante du container !!!
        $container = Container::$instance;
        $creator = $container->make(ServerRequestCreator::class);

        return $creator->fromGlobals();
    }
}
