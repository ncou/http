<?php

declare(strict_types=1);

namespace Chiron\Http\Exception;

use Chiron\Http\Exception\Client\ForbiddenHttpException;

/**
 * Represents an HTTP Forbidden (error 403) caused by a forbidden operation.
 */
final class PermissionDeniedException extends ForbiddenHttpException
{
    // TODO : ne pas permettre de modifier le messsage via le constructeur.
    // TODO : je pense que si on ajoute une variable protected de classe nommée "message" on doit pouvoir modifier le message en seulement 1 ligne de code !!!!
    public function __construct(?string $detail = null)
    {
        if ($detail === null) {
            $detail = 'The user did not have permission to do that';
        }

        parent::__construct($detail);
    }
}
