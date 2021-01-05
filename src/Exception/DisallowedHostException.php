<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Chiron\Http\Exception\Client\PreconditionFailedHttpException;

/**
 * Represents an HTTP BadRequest (error 400) caused by a disallowed host.
 */
final class DisallowedHostException extends SuspiciousOperationException
{
    // TODO : ne pas permettre de modifier le messsage via le constructeur.
    // TODO : je pense que si on ajoute une variable protected de classe nommée "message" on doit pouvoir modifier le message en seulement 1 ligne de code !!!!
    public function __construct(string $host)
    {
        // TODO : utiliser simplement le message suivant : 'HTTP_HOST header contains invalid value' car c'est un message qui sera affiché à l'utilisateur dans son naviagateur donc inutile de lui donner trop d'information sur le fonctionnement interne de l'application !!!!
        $detail = sprintf('Invalid Host header "%s". You may need to add this value to http.ALLOWED_HOSTS', $host);

        parent::__construct($detail);
    }
}
