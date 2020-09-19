<?php

declare(strict_types=1);

namespace Chiron\Http\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Http\SapiDispatcher;

final class SapiDispatcherBootloader extends AbstractBootloader
{
    public function boot(Application $application, AppConfig $config): void
    {
        $application->addDispatcher(resolve(SapiDispatcher::class));
    }
}
