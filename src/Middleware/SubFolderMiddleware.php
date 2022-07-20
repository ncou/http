<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use Chiron\Support\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Http\Traits\ParameterizedTrait;
use Chiron\Core\Exception\ImproperlyConfiguredException;

//https://github.com/yiisoft/yii-middleware/blob/master/src/SubFolder.php

//https://github.com/symfony/symfony/blob/3d00bafc2be5f40412bc9b968b222b9e24ca049e/src/Symfony/Component/HttpFoundation/Request.php#L837

// TODO : ajouter les tests !!!! https://github.com/yiisoft/yii-middleware/blob/master/tests/SubFolderTest.php

/*
//https://github.com/slimphp/Slim-Documentation-2.x/blob/b8692c766f6bf116c1669f2e99d89e8449e0f963/docs/environment/overview.md
SCRIPT_NAME : The initial portion of the request URI’s “path” that corresponds to the physical directory in which the Slim application is installed — so that the application knows its virtual “location”. This may be an empty string if the application is installed in the top-level of the public document root directory. This will never have a trailing slash.
*/

//The SCRIPT_NAME, if non-empty, must start with "/"
//One of SCRIPT_NAME or PATH_INFO must be set. PATH_INFO should be "/" if SCRIPT_NAME is empty. SCRIPT_NAME never should be "/", but instead be an empty string.

//https://github.com/slimphp/Slim-Website/blob/5000716061b50415b8d905528fde302754d2fcbb/docs/v3/cookbook/environment.md
//SCRIPT_NAME : The absolute path name to the front-controller PHP script relative to your document root, disregarding any URL rewriting performed by your web server.

/**
 * This middleware supports routing when webroot is not the same folder as public.
 */
final class SubFolderMiddleware implements MiddlewareInterface
{
    private ?string $prefix;

    /**
     * @param UrlGeneratorInterface $uriGenerator The URI generator instance.
     * @param Aliases $aliases The aliases instance.
     * @param string|null $prefix URI prefix the specified immediately after the domain part.
     * The prefix value usually begins with a slash and must not end with a slash.
     * @param string|null $alias The path alias {@see Aliases::get()}.
     */
    /*
    public function __construct(
        UrlGeneratorInterface $uriGenerator,
        Aliases $aliases,
        ?string $prefix = null,
        ?string $alias = null
    ) {
        $this->uriGenerator = $uriGenerator;
        $this->aliases = $aliases;
        $this->prefix = $prefix;
        $this->alias = $alias;
    }*/

    public function __construct(
        ?string $prefix = null
    ) {
        // TODO : je pense qu'il faudra forcer un rtrim($prefix, '/') pour gérer le cas ou on a un prefix basique avec une valeur "/" car sinon je pense qu'on aura un double slash lors de la concaténation et pour éviter l'erreur ligne 82 qui vérifie que le prefix ne se termine pas par un slash !!!!!
        // TODO : regarder dans django FORCE_SCRIPT_NAME ou dans cakephp ce qui est préconnisé pour le basepath si il doit se terminer ou non par un slash !!!!
        $this->prefix = $prefix; // '/nano5/public';
    }

    /**
     * The Uri's path is corrected to only contain the 'virtual' path for the request.
     *
     * @throws ImproperlyConfiguredException If wrong URI prefix.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $prefix = $this->prefix;
        $auto = $prefix === null;
        /** @var string $prefix */
        $length = $auto ? 0 : strlen($prefix);

        if ($auto) {
            // automatically checks that the project is in a subfolder
            // and URI contains a prefix
            $scriptName = $request->getServerParams()['SCRIPT_NAME'];

            if (is_string($scriptName) && strpos($scriptName, '/', 1) !== false) {
                $position = strrpos($scriptName, '/');
                $tmpPrefix = substr($scriptName, 0, $position === false ? null : $position);

                if (strpos($path, $tmpPrefix) === 0) {
                    $prefix = $tmpPrefix;
                    $length = strlen($prefix);
                }
            }
        } elseif ($length > 0) {
            // TODO : je pense que le prefix doit commencer impérativement par un "/" il faudrait surement faire ce controle !!!!
            /** @var string $prefix */
            if ($prefix[-1] === '/') {
                throw new ImproperlyConfiguredException('Wrong URI prefix value.');
            }

            if (strpos($path, $prefix) !== 0) {
                throw new ImproperlyConfiguredException('URI prefix does not match.');
            }
        }

        if ($length > 0) {
            $newPath = substr($path, $length);

            if ($newPath === '') {
                $newPath = '/';
            }

            if ($newPath[0] !== '/') {
                if (!$auto) {
                    throw new ImproperlyConfiguredException('URI prefix does not match completely.');
                }
            } else {
                $request = $request->withUri($uri->withPath($newPath));
                /** @var string $prefix */
                //$this->uriGenerator->setUriPrefix($prefix);

                //if ($this->alias !== null) {
                //    $this->aliases->set($this->alias, $prefix . '/');
                //}
            }
        }

        return $handler->handle($request);
    }
}
