<?php

declare(strict_types=1);

namespace Chiron\Http\Provider;

use Chiron\Container\BindingInterface;
use Chiron\Container\Container;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Http\ResponseWrapper;
use Http\Factory\Psr17FactoryFinder;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

//https://github.com/php-services/http-factory-nyholm/blob/master/src/NyholmHttpFactoryServiceProvider.php

//https://github.com/userfrosting/UserFrosting/blob/master/app/system/ServicesProvider.php
//https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php

/**
 * Chiron http factories services provider.
 */
final class HttpFactoriesServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron http factories services.
     *
     * @param BindingInterface $binder
     */
    public function register(BindingInterface $binder): void
    {
        // *** register factories ***
        /*
        $binder->bind(ResponseFactoryInterface::class, function () {
            $factory = Psr17FactoryFinder::findResponseFactory();
            $headers = []; // TODO : aller rechercher dans la classe httpConfig les headers de base à injecter dans la réponse.

            return new ResponseWrapper($factory, $headers);
        });*/

        // TODO : faire plutot des ->singleton pour économiser de la mémoire/temps ????

        $binder->bind(ResponseFactoryInterface::class, [Psr17FactoryFinder::class, 'findResponseFactory']);
        $binder->bind(RequestFactoryInterface::class, [Psr17FactoryFinder::class, 'findRequestFactory']);
        $binder->bind(ServerRequestFactoryInterface::class, [Psr17FactoryFinder::class, 'findServerRequestFactory']);
        $binder->bind(UriFactoryInterface::class, [Psr17FactoryFinder::class, 'findUriFactory']);
        $binder->bind(UploadedFileFactoryInterface::class, [Psr17FactoryFinder::class, 'findUploadedFileFactory']);
        $binder->bind(StreamFactoryInterface::class, [Psr17FactoryFinder::class, 'findStreamFactory']);

        // *** register alias ***
        $this->registerAlias($binder); // TODO : vérifier l'utilité des alias !!!!
    }

    private function registerAlias(BindingInterface $binder): void
    {
        $binder->alias('responseFactory', ResponseFactoryInterface::class);
        $binder->alias('requestFactory', RequestFactoryInterface::class);
        $binder->alias('serverRequestFactory', ServerRequestFactoryInterface::class);
        $binder->alias('uriFactory', UriFactoryInterface::class);
        $binder->alias('uploadedFileFactory', UploadedFileFactoryInterface::class);
        $binder->alias('streamFactory', StreamFactoryInterface::class);
    }
}
