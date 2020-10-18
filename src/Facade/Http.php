<?php

declare(strict_types=1);

namespace Chiron\Http\Facade;

use Chiron\Core\Facade\AbstractFacade;

final class Http extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
        return \Chiron\Http\Http::class;
    }
}
