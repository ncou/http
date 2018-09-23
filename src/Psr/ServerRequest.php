<?php

declare(strict_types=1);

namespace Chiron\Http\Psr;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Michael Dowling and contributors to guzzlehttp/psr7
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /** @var array */
    private $attributes = [];

    /** @var array */
    private $cookieParams = [];

    /** @var null|array|object */
    private $parsedBody;

    /** @var array */
    private $queryParams = [];

    /** @var array */
    private $serverParams;

    /** @var UploadedFileInterface[] */
    private $uploadedFiles = [];

    /**
     * @param string               $method       HTTP method
     * @param UriInterface         $uri          URI
     * @param array                $headers      Request headers
     * @param StreamInterface|null $body         Request body
     * @param string               $version      Protocol version
     * @param array                $serverParams Typically the $_SERVER superglobal
     */
    public function __construct(
        $method,
        UriInterface $uri,
        array $headers = [],
        StreamInterface $body = null,
        $version = '1.1',
        array $serverParams = []
    ) {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $version);
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data)
    {
        if (! is_array($data) && ! is_object($data) && null !== $data) {
            throw new \InvalidArgumentException('First parameter to withParsedBody MUST be object, array or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($attribute, $default = null)
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    public function withAttribute($attribute, $value): self
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function withoutAttribute($attribute): self
    {
        if (false === array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);

        return $new;
    }

    private $contentTypes;

    private $languages;

    /**
     * @param string                               $method       HTTP method
     * @param string|UriInterface                  $uri          URI
     * @param array                                $headers      Request headers
     * @param string|null|resource|StreamInterface $body         Request body
     * @param string                               $version      Protocol version
     * @param array                                $serverParams Typically the $_SERVER superglobal
     */
    /*
    public function __construct(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1',
        array $serverParams = []
    ) {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $version, $serverParams);
    }*/

    /**
     * Check if the cookie exist in the request.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCookie(string $name): bool
    {
        return array_key_exists($name, $this->getCookieParams());
    }

    /**
     * Create a new instance with the specified derived request attributes.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method allows setting all new derived request attributes as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated attributes.
     *
     * @param array $attributes New attributes
     *
     * @return static
     */
    // TODO : il faudrait pas plutot faire un array_merge ? ou un array_replace ? pour éviter de perdre les attributs existants
    // TODO : créer une méthode withoutAttributes pour vider tous les attributs. non ? ou directement un setAttributes($array, $merge = false) ?
    public function withAttributes(array $attributes)
    {
        $clone = clone $this;
        $clone->attributes = $attributes;

        return $clone;
    }

    /**
     * Does this request use a given method?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $method HTTP method
     *
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        // TODO : on devrait pas faire un === aprés avoir fait un lowercase sur la méthode ?
        //return $this->getMethod() === $method;
        return strcasecmp($this->getMethod(), $method) === 0;
    }

    /**
     * Is this a GET request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * Is this a POST request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * Is this a PUT request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    /**
     * Is this a PATCH request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod('PATCH');
    }

    /**
     * Is this a DELETE request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Is this a HEAD request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isHead()
    {
        return $this->isMethod('HEAD');
    }

    /**
     * Is this a OPTIONS request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->isMethod('OPTIONS');
    }

    /**
     * Is this a PURGE request? ('PURGE' is not an official method described in RFC).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isPurge()
    {
        return $this->isMethod('PURGE');
    }

    /**
     * Returns whether this is an AJAX (XMLHttpRequest) request.
     *
     * Note that jQuery doesn't set the header in case of cross domain
     * requests: https://stackoverflow.com/questions/8163703/cross-domain-ajax-doesnt-send-x-requested-with-header
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool whether this is an AJAX (XMLHttpRequest) request
     */
    public function isAjax()
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Is this an XHR request? it's an alias for isAjax().
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isXhr()
    {
        return $this->isAjax();
    }

    /**
     * Returns whether this is a PJAX request.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool whether this is a PJAX request
     */
    public function IsPjax()
    {
        return $this->IsAjax() && $this->hasHeader('X-Pjax');
    }

    /**
     * Get request content type.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request content type, if known
     */
    public function getContentType()
    {
        $result = $this->getHeader('Content-Type');

        return $result ? $result[0] : null;
    }

    /**
     * Get request media type, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null The request media type, minus content-type params
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }
    }

    public function getMediaType2()
    {
        $type = strtolower($request->getHeaderLine('Content-Type'));
        list($type) = explode(';', $type);

        return $type;
    }

    private function getMediaType3(ServerRequestInterface $request)
    {
        $contentType = $request->hasHeader('Content-Type') ? $request->getHeaderLine('Content-Type') : null;

        if ($contentType) {
            $parts = explode(';', $request->getHeaderLine('Content-Type'));

            return strtolower(trim(array_shift($parts)));
        }
    }

    /**
     * Get request media type params, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return array
     */
    public function getMediaTypeParams()
    {
        $contentType = $this->getContentType();
        $contentTypeParams = [];
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    /**
     * Get request content character set, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return string|null
     */
    public function getContentCharset()
    {
        $mediaTypeParams = $this->getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        }
    }

    /**
     * Get request content length, if known.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return int|null
     */
    public function getContentLength()
    {
        $result = $this->headers->get('Content-Length');

        return $result ? (int) $result[0] : null;
    }

    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     *
     * @return bool
     */
    /*
        public function isMethod($method)
        {
            return $this->getMethod() === strtoupper($method);
        }
    */

    /**
     * Is the request secure?
     *
     * @return bool
     */
    /*
    public function isSecure()
    {
        $https = $this->getServerParam('HTTPS');

        return ! empty($https) && ('off' !== strtolower($https));
    }*/

    public function isSecure()
    {
        return $this->getScheme() === 'https';
    }

    // it's an alias for the function isSecure()
    public function isSsl()
    {
        return $this->isSecure();
    }

    //https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    // TODO : helper à virer, attention à bien modifier la méthode isSecure qui est dépendante de cette méthode !!!!
    public function getScheme()
    {
        return $this->getUri()->getScheme();
    }

    //*******************************************
    // https://github.com/Guzzle3/http/blob/master/Message/Request.php
    //******************************************* START ******************************************

    public function getPath()
    {
        return '/' . ltrim($this->getUri()->getPath(), '/');
    }

    public function getPort()
    {
        return $this->getUri()->getPort();
    }

    /*******************************************************************************
     * Parameters (e.g., POST and GET data)
     ******************************************************************************/

    /**
     * Fetch parameter value from query string.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getQueryParam(string $key, $default = null)
    {
        $getParams = $this->getQueryParams();
        $result = $default;
        if (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Fetch cookie value from cookies sent by the client to the server.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $name    the cookie name
     * @param mixed  $default default value to return if the attribute does not exist
     *
     * @return mixed
     */
    public function getCookieParam(string $name, $default = null)
    {
        if (! $this->hasCookie($name)) {
            return $default;
        }

        return $this->getCookieParams()[$name];
    }

    /**
     * Retrieve a server parameter.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getServerParam(string $key, $default = null)
    {
        $serverParams = $this->getServerParams();

        return isset($serverParams[$key]) ? $serverParams[$key] : $default;
    }

    /**
     * Fetch parameter value from request body.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParsedBodyParam(string $key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        }

        return $result;
    }

    // TODO : il faudrait éventuellement créer la méthode : getUploadedFile(string $name, $default = null);

    /**
     * Fetch request parameter value from body or query string (in that order).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key     the parameter key
     * @param mixed  $default the default value
     *
     * @return mixed the parameter value
     */
    // TODO : méthode pas vraiment utile !!!
    public function getParam(string $key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $getParams = $this->getQueryParams();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Fetch associative array of body and query string parameters.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param array|null $only list the keys to retrieve
     *
     * @return array|null
     */
    // TODO : méthode pas vraiment utile !!!
    public function getParams(array $only = null)
    {
        $params = $this->getQueryParams();
        $postParams = $this->getParsedBody();
        if ($postParams) {
            $params = array_merge($params, (array) $postParams);
        }
        if ($only) {
            $onlyParams = [];
            foreach ($only as $key) {
                if (array_key_exists($key, $params)) {
                    $onlyParams[$key] = $params[$key];
                }
            }

            return $onlyParams;
        }

        return $params;
    }
}
