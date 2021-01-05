<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Chiron\Http\Exception\Client\BadRequestHttpException;

/**
 * Represents an HTTP BadRequest (error 400) caused by a suspicious operation.
 */
class SuspiciousOperationException extends BadRequestHttpException
{
    // TODO : ne pas permettre de modifier le messsage via le constructeur.
    // TODO : je pense que si on ajoute une variable protected de classe nommée "message" on doit pouvoir modifier le message en seulement 1 ligne de code !!!!
    public function __construct(?string $detail = null)
    {
        if ($detail === null) {
            $detail = 'The user did something suspicious';
        }

        parent::__construct($detail);
    }
}
