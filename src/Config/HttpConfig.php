<?php

declare(strict_types=1);

namespace Chiron\Http\Config;

use Chiron\Config\AbstractInjectableConfig;
use Chiron\Config\Helper\Validator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

// TODO : control basique pour vérifier que le charset est valide.
// charset should have at least a length of 5 char, start with a letter and be alphanumeric with "_" or "-" as special char
/*
        if (! preg_match('/^[a-z][a-z0-9_-]{4,}$/i', $charset)) {
            throw new InvalidArgumentException('Invalid charset value'); // TODO : utiliser un ImproperlyConfigured !!!!
        }*/

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
            'middlewares'      => Expect::array(), // TODO : vérifier que la valeur est une string ou un objet de type MiddlewareBinding. Et je pense qu'on pourrait aussi lui passer une instance d'un MiddlewareInterface !!!!
            'handle_exception' => Expect::bool()->default(true),
            'tag_request'      => Expect::bool()->default(true),
            'allowed_hosts'    => Expect::listOf('string'),
            'disallowed_user_agents' => Expect::listOf('string'),
            'default_charset'        => Expect::string()->default('utf-8'),
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
        // TODO : je pense qu'il faut forcer la valeur à "/" dans le cas ou l'utilisateur a laissé vide. voir même gérer le cas ou un idot met './' pour le le base_pass ou même '.' Le plus simple est surement de faire un ltrim('/') et ensuite de retourner la valeur avec "'/' . $value" comme ca on est sur d'avoir le slash au début de la chaine
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

    public function getTagRequest(): bool
    {
        return $this->get('tag_request');
    }

    public function getAllowedHosts(): array
    {
        return $this->get('allowed_hosts');
    }

    public function getDisallowedUserAgents(): array
    {
        return $this->get('disallowed_user_agents');
    }

    public function getDefaultCharset(): string
    {
        return $this->get('default_charset');
    }

    /*
    // TODO : voir pour utiliser ce bout de code !!!!
    function isCharset($charset = '')
    {
        if( ! is_string($charset) )
        {
            return false;
        }

        if( array_search(strtolower($charset), array_map('strtolower', mb_list_encodings()), true) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    */
}
