<?php

declare(strict_types=1);

namespace Chiron\Http\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpFoundation/Response.php#L259
// https://github.com/cakephp/cakephp/blob/5.x/src/Http/Response.php#L517

// https://github.com/cakephp/cakephp/blob/dd9d8d563cb934daf0d564acf25f1b5308fae65a/src/Http/Response.php#L494
// https://github.com/cakephp/cakephp/blob/5.x/src/Http/Response.php#L502

// TODO : utiliser la regex suivante pour extraire le charset (tiré de django) : _charset_from_content_type_re = re.compile(r';\s*charset=(?P<charset>[^\s;]+)', re.I)
//https://docs.djangoproject.com/fr/2.1/_modules/django/http/response/
//https://github.com/django/django/blob/bb61f0186d5c490caa44f3e3672d81e14414d33c/django/http/response.py#L24

// TODO : ajouter ce champ default_charset dans le fichier de config http.php comme c'est fait par django.
//https://docs.djangoproject.com/en/4.0/ref/settings/#default-charset

/**
 * Add a default charset if the "Content-Type" header is found and there is not already a charset defined in this header.
 */
final class CharsetByDefaultMiddleware implements MiddlewareInterface
{
    private string $charset;

    /**
     * Configure the default charset.
     *
     * @param string $charset Default charset to use in http response.
     */
    public function __construct(string $charset)
    {
        $this->charset = strtolower($charset);
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $this->withDefaultCharset($response);
    }

    /**
     * If needed add the default charset for the Content-Type header.
     *
     *  @see https://tools.ietf.org/html/rfc7231#section-3.1.1.2
     */
    private function withDefaultCharset(ResponseInterface $response): ResponseInterface
    {
        // We can't add the default charset if there is not the Content-Type header.
        if (! $response->hasHeader('Content-Type')) {
            return $response;
        }

        $contentType = strtolower($response->getHeaderLine('Content-Type'));

        if (! str_contains($contentType, 'charset')) {
            if ($this->isResponseTextual($contentType)) {
                // Add the charset to the content-type header.
                return $response->withHeader('Content-Type', $contentType . '; charset=' . $this->charset);
            }
        }

        return $response;
    }

    private function isResponseTextual(string $contentType): bool
    {
        // TODO : utiliser la classe Mime pour avoir une fonction pour récupérer le mediapart et vérifier si le mime est textuel ???
        // https://github.com/cakephp/cakephp/blob/5.x/src/Http/Response.php#L502

        // Allow a bunch of representation who will be textual.
        $allowed = [
            'application/javascript',
            'application/json',
            'application/xml',
            'application/rss+xml',
            'application/atom+xml',
            'application/xhtml',
            'application/xhtml+xml'
        ];

        // extract the media(mime) part from the Content-Type header
        $parts = explode(';', $contentType);
        $mediaType = trim(array_shift($parts));

        $isTextualOrAllowed = (str_starts_with($contentType, 'text/') || in_array($mediaType, $allowed));

        return $isTextualOrAllowed;
    }
}
