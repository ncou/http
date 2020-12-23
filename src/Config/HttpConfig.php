<?php

declare(strict_types=1);

namespace Chiron\Http\Config;

use Chiron\Config\AbstractInjectableConfig;
use Chiron\Config\Helper\Validator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class HttpConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'http';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            // TODO : à virer on utilisera un service provider pour modifier la création de l'object et donc changer le constructeur.
            'buffer_size'      => Expect::int()->default(8 * 1024 * 1024),
            'protocol'         => Expect::string()->default('1.1'),
            // TODO : champ à déplacer dans un fichier "routing.php" ???? car c'est pas vraiment un paramétrage du protocol http !!!!
            'base_path'        => Expect::string()->default('/'),
            'headers'          => Expect::arrayOf('string')->assert([Validator::class, 'isArrayAssociative'], 'associative array'),
            'middlewares'      => Expect::listOf('string'),
            'handle_exception' => Expect::bool()->default(true),
        ]);
    }

    public function getBufferSize(): int
    {
        return $this->get('buffer_size');
    }

    public function getProtocol(): string
    {
        return $this->get('protocol');
    }

    public function getBasePath(): string
    {
        return $this->get('base_path');
    }

    public function getHeaders(): array
    {
        return $this->get('headers');
    }

    public function getMiddlewares(): array
    {
        return $this->get('middlewares');
    }

    public function getHandleException(): bool
    {
        return $this->get('handle_exception');
    }
}
