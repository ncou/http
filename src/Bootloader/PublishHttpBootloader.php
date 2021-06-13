<?php

declare(strict_types=1);

namespace Chiron\Http\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Core\Directories;
use Chiron\Publisher\Publisher;

final class PublishHttpBootloader extends AbstractBootloader
{
    public function boot(Publisher $publisher, Directories $directories): void
    {
        $publisher->add(__DIR__ . '/../../config/http.php.dist', $directories->get('@config/http.php'));
    }
}
