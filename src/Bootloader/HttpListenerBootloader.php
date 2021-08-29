<?php

declare(strict_types=1);

namespace Chiron\Http\Bootloader;

use Chiron\Container\Container;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Http;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Http\Middleware\AllowedHostsMiddleware;
use Chiron\Http\Middleware\NotFoundDebugMiddleware;
use Chiron\Http\Middleware\TagRequestMiddleware;
use Chiron\Core\Exception\BootException;
use Chiron\Event\ListenerProvider;
use Chiron\Http\Listener\PipelineListener;

final class HttpListenerBootloader extends AbstractBootloader
{
    // TODO : eventuellement virer le container dans la signature de la méthode et ajouter un FactoryInterface pour faire un ->build(XXXListener::class)
    public function boot(ListenerProvider $listener, Container $container): void
    {
        $listener->add(new PipelineListener($container));
    }
}
