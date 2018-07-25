<?php

declare(strict_types=1);

namespace Chiron\Http\Psr;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @author Michael Dowling and contributors to guzzlehttp/psr7
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Response implements ResponseInterface
{
    use MessageTrait;

    /** @var array Map of standard HTTP status code/reason phrases */
    private static $phrases = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /** @var string */
    private $reasonPhrase = '';

    /** @var int */
    private $statusCode = 200;

    /**
     * @param int                  $status  Status code
     * @param array                $headers Response headers
     * @param StreamInterface|null $body    Response body
     * @param string               $version Protocol version
     * @param string|null          $reason  Reason phrase (when empty a default will be used based on the status code)
     */
    public function __construct(
        int $status = 200,
        array $headers = [],
        StreamInterface $body = null,
        string $version = '1.1',
        $reason = null
    ) {
        $this->statusCode = (int) $status;

        $this->stream = $body ?? new Stream(fopen('php://temp', 'r+'));

        $this->setHeaders($headers);
        if (null === $reason && isset(self::$phrases[$this->statusCode])) {
            $this->reasonPhrase = self::$phrases[$status];
        } else {
            $this->reasonPhrase = (string) $reason;
        }

        $this->protocol = $version;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        if (! is_int($code) && ! is_string($code)) {
            throw new \InvalidArgumentException('Status code has to be an integer');
        }

        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw new \InvalidArgumentException('Status code has to be an integer between 100 and 599');
        }

        $new = clone $this;
        $new->statusCode = (int) $code;
        if ('' == $reasonPhrase && isset(self::$phrases[$new->statusCode])) {
            $reasonPhrase = self::$phrases[$new->statusCode];
        }
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    public const FORMAT_URLENCODED = 'URLENCODED';

    public const FORMAT_JSON = 'JSON';

    public const FORMAT_XML = 'XML';

    // TODO : il faudrait pas implémenter une méthode clone avec les objets genre header ou cookies ????     https://github.com/slimphp/Slim/blob/3.x/Slim/Http/Response.php#L147
    // TODO : les cookies ne semble pas avoir leur place ici !!!!!!!!!!
    private $cookies = [];

    // https://github.com/guzzle/guzzle3/blob/master/src/Guzzle/Http/Message/Response.php#L99

    /** @var array Cacheable response codes (see RFC 2616:13.4) */
    protected static $cacheResponseCodes = [200, 203, 206, 300, 301, 410];

    // 200, 203, 300, 301, 302, 404, 410
    // TODO : regarder ici la liste : https://github.com/micheh/psr7-cache/blob/master/src/CacheUtil.php#L289

    // TODO : vérifier si on garde l'initialisation du ProtocolVersion en trant que paramétre du constructeur
    // TODO : virer la partie "reason" du constructeur ?????
    //@param string|resource|StreamInterface $body Stream identifier and/or actual stream resource

    //public function __construct($status = 200, $body = 'php://temp', $reason = '', $version = '1.1', array $headers = [])
    /*
    public function __construct(int $status = 200, array $headers = [], $body = null, string $version = '1.1', $reason = null)
    {
        parent::__construct($status, $headers, $body, $version, $reason);
    }*/

    /**
     * Return the reason phrase by code.
     *
     * @param $code
     *
     * @return string
     */
    /*
    // NOT A PSR7 FUNCTION
    public static function getReasonPhraseByCode($code): string
    {
        return self::$phrases[$code] ?? '';
    }
    */

    /**
     * Set a valid status code.
     *
     * @param int $code
     *
     * @throws InvalidArgumentException on an invalid status code
     */
    // NOT A PSR7 FUNCTION
    //https://github.com/phly/http/blob/master/src/Response.php#L167
    /*
    protected function setStatusCode($code)
    {
        if (! is_numeric($code)
            || is_float($code)
            || $code < static::MIN_STATUS_CODE_VALUE
            || $code > static::MAX_STATUS_CODE_VALUE
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between %d and %d, inclusive',
                (is_scalar($code) ? $code : gettype($code)),
                static::MIN_STATUS_CODE_VALUE,
                static::MAX_STATUS_CODE_VALUE
            ));
        }
        $this->statusCode = $code;
    }*/

    /*******************************************************************************
     * Body
     ******************************************************************************/

    /**
     * Write data to the response body.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * Proxies to the underlying stream and writes the provided data to it.
     *
     * @param string $data
     *
     * @return $this
     */
    public function write(string $data)
    {
        $this->getBody()->write($data);

        return $this;
    }
    // TODO : creer aussi un helper .rewind() qui serait un shortcut pour faire un body->rewrite() ????

    //--------------------------
    // https://github.com/swoft-cloud/framework/blob/master/src/Base/Response.php

    /**
     * @var string
     */
    //protected $charset = 'utf-8';

    /**
     * Return an instance with the specified charset content type.
     *
     * @param $charset
     *
     * @return static
     */
    /*
    public function withCharset($charset): self
    {
        $clone = clone $this;
        $clone->withAddedHeader('Content-Type', sprintf('charset=%s', $charset));
        return $clone;
    }
    */

    /**
     * @return string
     */
    /*
    public function getCharset(): string
    {
        return $this->charset;
    }
    */
    /**
     * @param string $charset
     *
     * @return Response
     */
    /*
    public function setCharset(string $charset): Response
    {
        $this->charset = $charset;
        return $this;
    }
    */

    //https://github.com/cakephp/cakephp/blob/master/src/Http/Response.php#L1170
    /**
     * The charset the response body is encoded with.
     *
     * @var string
     */
    //protected $_charset = 'UTF-8';
    /**
     * Sets the response charset
     * if $charset is null the current charset is returned.
     *
     * @param string|null $charset character set string
     *
     * @return string Current charset
     *
     * @deprecated 3.5.0 Use getCharset()/withCharset() instead.
     */
    /*
    public function charset($charset = null)
    {
        if ($charset === null) {
            return $this->_charset;
        }
        $this->_charset = $charset;
        $this->_setContentType();
        return $this->_charset;
    }*/
    /**
     * Returns the current charset.
     *
     * @return string
     */
    /*
    public function getCharset()
    {
        return $this->_charset;
    }*/
    /**
     * Get a new instance with an updated charset.
     *
     * @param string $charset character set string
     *
     * @return static
     */
    /*
    public function withCharset($charset)
    {
        $new = clone $this;
        $new->_charset = $charset;
        $new->_setContentType();
        return $new;
    }*/

    /**
     * Refreshes the current page.
     * The effect of this method call is the same as the user pressing the refresh button of his browser
     * (without re-posting data).
     *
     * In a controller action you may use this method like this:
     *
     * ```php
     * return Yii::$app->getResponse()->refresh();
     * ```
     *
     * @param string $anchor the anchor that should be appended to the redirection URL.
     *                       Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     *
     * @return Response the response object itself
     */
    /*
    public function refresh($anchor = '')
    {
        return $this->redirect(Yii::$app->getRequest()->getUrl() . $anchor);
    }*/

    /**
     * Sets the response status code based on the exception.
     *
     * @param \Exception|\Error $e the exception object
     *
     * @throws InvalidArgumentException if the status code is invalid
     *
     * @return $this the response object itself
     *
     * @since 2.0.12
     */
    //https://github.com/yiisoft/yii2/blob/master/framework/web/Response.php#L303
    /*
    public function setStatusCodeByException($e)
    {
        if ($e instanceof HttpException) {
            $this->setStatusCode($e->statusCode);
        } else {
            $this->setStatusCode(500);
        }
        return $this;
    }*/

    /**
     * Is the response OK?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->getStatusCode() === 200;
    }

    /**
     * Is the response empty?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return in_array($this->getStatusCode(), [204, 304]);
    }

    /**
     * Is this response empty?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    /*
    public function isEmpty()
    {
        return in_array($this->getStatusCode(), [204, 205, 304]);
    }*/

    /**
     * @return bool whether this response is empty
     */
    /*
    public function getIsEmpty()
    {
        return in_array($this->getStatusCode(), [201, 204, 304]);
    }*/

    /**
     * Is the response a redirect of some form?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isRedirect(): bool
    {
        return in_array($this->getStatusCode(), [301, 302, 303, 307, 308]);
    }

    /**
     * Is this response a redirect?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    /*
    public function isRedirect()
    {
        return in_array($this->getStatusCode(), [301, 302, 303, 307]);
    }*/

    /**
     * Is response invalid?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @return bool
     */
    public function isInvalid(): bool
    {
        return $this->getStatusCode() < 100 || $this->getStatusCode() >= 600;
    }

    /**
     * Is response informative?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isInformational(): bool
    {
        return $this->getStatusCode() >= 100 && $this->getStatusCode() < 200;
    }

    /**
     * Is response successful?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }

    /**
     * Checks if HTTP Status code is Successful (2xx | 304).
     *
     * @return bool
     */
    /*
    public function isSuccessful()
    {
        return ($this->getStatusCode() >= 200 && $this->getStatusCode() < 300) || $this->getStatusCode() == 304;
    }*/

    /**
     * Is the response a redirect?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isRedirection(): bool
    {
        return $this->getStatusCode() >= 300 && $this->getStatusCode() < 400;
    }

    /**
     * Is there a client error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * Was there a server side error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * Checks if HTTP Status code is Server OR Client Error (4xx or 5xx).
     *
     * @return bool
     */
    public function isError(): bool
    {
        return $this->isClientError() || $this->isServerError();
    }

    /**
     * Is the response forbidden?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isForbidden(): bool
    {
        return $this->getStatusCode() === 403;
    }

    /**
     * Is the response a not found error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isNotFound(): bool
    {
        return $this->getStatusCode() === 404;
    }

    /**
     * Is the response a method not allowed error?
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isMethodNotAllowed(): bool
    {
        return $this->getStatusCode() === 405;
    }

    /**
     * @return array the formatters that are supported by default
     */
    //https://github.com/yiisoft/yii2/blob/master/framework/web/Response.php#L1005
    /*
    protected function defaultFormatters()
    {
        return [
            self::FORMAT_HTML => [
                'class' => 'yii\web\HtmlResponseFormatter',
            ],
            self::FORMAT_XML => [
                'class' => 'yii\web\XmlResponseFormatter',
            ],
            self::FORMAT_JSON => [
                'class' => 'yii\web\JsonResponseFormatter',
            ],
            self::FORMAT_JSONP => [
                'class' => 'yii\web\JsonResponseFormatter',
                'useJsonp' => true,
            ],
        ];
    }*/

    /**
     * Sets the Date header.
     *
     * @return $this
     *
     * @final
     */
    //https://github.com/symfony/http-foundation/blob/master/Response.php#L649
    public function setDate(\DateTimeInterface $date): self
    {
        if ($date instanceof \DateTime) {
            $date = \DateTimeImmutable::createFromMutable($date);
        }
        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers->set('Date', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Sets the ETag value.
     *
     * @param string|null $etag The ETag unique identifier or null to remove the header
     * @param bool        $weak Whether you want a weak ETag or not
     *
     * @return $this
     *
     * @final
     */
    public function setEtag(string $etag = null, bool $weak = false): self
    {
        if (null === $etag) {
            $this->headers->remove('Etag');
        } else {
            if (0 !== strpos($etag, '"')) {
                $etag = '"' . $etag . '"';
            }
            $this->headers->set('ETag', (true === $weak ? 'W/' : '') . $etag);
        }

        return $this;
    }

    /**
     * Sets the response's cache headers (validation and/or expiration).
     *
     * Available options are: etag, last_modified, max_age, s_maxage, private, public and immutable.
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     *
     *
     * @final
     */
    public function setCache(array $options): self
    {
        if ($diff = array_diff(array_keys($options), ['etag', 'last_modified', 'max_age', 's_maxage', 'private', 'public', 'immutable'])) {
            throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', array_values($diff))));
        }
        if (isset($options['etag'])) {
            $this->setEtag($options['etag']);
        }
        if (isset($options['last_modified'])) {
            $this->setLastModified($options['last_modified']);
        }
        if (isset($options['max_age'])) {
            $this->setMaxAge($options['max_age']);
        }
        if (isset($options['s_maxage'])) {
            $this->setSharedMaxAge($options['s_maxage']);
        }
        if (isset($options['public'])) {
            if ($options['public']) {
                $this->setPublic();
            } else {
                $this->setPrivate();
            }
        }
        if (isset($options['private'])) {
            if ($options['private']) {
                $this->setPrivate();
            } else {
                $this->setPublic();
            }
        }
        if (isset($options['immutable'])) {
            $this->setImmutable((bool) $options['immutable']);
        }

        return $this;
    }

    /**
     * Returns an array of header names given in the Vary header.
     *
     * @final
     */
    /*
    public function getVary(): array
    {
        if (!$vary = $this->headers->get('Vary', null, false)) {
            return array();
        }
        $ret = array();
        foreach ($vary as $item) {
            $ret = array_merge($ret, preg_split('/[\s,]+/', $item));
        }
        return $ret;
    }*/

    /**
     * Sets the Vary header.
     *
     * @param string|array $headers
     * @param bool         $replace Whether to replace the actual value or not (true by default)
     *
     * @return $this
     *
     * @final
     */
    public function setVary($headers, bool $replace = true)
    {
        $this->headers->set('Vary', $headers, $replace);

        return $this;
    }

    /**
     * Get the Accept-Ranges HTTP header.
     *
     * @return string returns what partial content range types this server supports
     */
    public function getAcceptRanges(): string
    {
        return $this->getHeaderLine('Accept-Ranges');
    }

    /**
     * Get the Age HTTP header.
     *
     * @return int|null returns the age the object has been in a proxy cache in seconds, or null if header not present
     */
    public function getAge(): ?int
    {
        return $this->hasHeader('Age') ? (int) $this->getHeaderLine('Age') : null;
    }

    /**
     * Get the Allow HTTP header.
     *
     * @return string[]| null Returns valid actions for a specified resource, or empty array. To be used for a 405 Method not allowed.
     */
    public function getAllow(): ?array
    {
        return $this->hasHeader('Allow') ? array_map('trim', explode(',', $this->getHeaderLine('Allow'))) : null;
    }

    /**
     * Check if an HTTP method is allowed by checking the Allow response header.
     *
     * @param string $method Method to check
     *
     * @return bool
     */
    public function isMethodAllowed(string $method): bool
    {
        $methods = $this->getAllow();
        if ($methods) {
            return in_array(strtoupper($method), $methods);
        }

        return false;
    }

    /**
     * Get the Cache-Control HTTP header.
     *
     * @return string
     */
    public function getCacheControl()
    {
        return $this->getHeaderLine('Cache-Control');
    }

    /**
     * Get the Connection HTTP header.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->getHeaderLine('Connection');
    }

    /**
     * Get the Content-Encoding HTTP header.
     *
     * @return string|null
     */
    public function getContentEncoding()
    {
        return $this->getHeaderLine('Content-Encoding');
    }

    /**
     * Get the Content-Language HTTP header.
     *
     * @return string|null returns the language the content is in
     */
    public function getContentLanguage()
    {
        return $this->getHeaderLine('Content-Language');
    }

    /**
     * Get the Content-Length HTTP header.
     *
     * @return int Returns the length of the response body in bytes
     */
    public function getContentLength()
    {
        return (int) $this->getHeaderLine('Content-Length');
    }

    /**
     * Get the Content-Location HTTP header.
     *
     * @return string|null Returns an alternate location for the returned data (e.g /index.htm)
     */
    public function getContentLocation()
    {
        return $this->getHeaderLine('Content-Location');
    }

    /**
     * Get the Content-Disposition HTTP header.
     *
     * @return string|null Returns the Content-Disposition header
     */
    public function getContentDisposition()
    {
        return $this->getHeaderLine('Content-Disposition');
    }

    /**
     * Get the Content-MD5 HTTP header.
     *
     * @return string|null returns a Base64-encoded binary MD5 sum of the content of the response
     */
    public function getContentMd5()
    {
        return $this->getHeaderLine('Content-MD5');
    }

    /**
     * Get the Content-Range HTTP header.
     *
     * @return string Returns where in a full body message this partial message belongs (e.g. bytes 21010-47021/47022).
     */
    public function getContentRange()
    {
        return $this->getHeaderLine('Content-Range');
    }

    /**
     * Get the Content-Type HTTP header.
     *
     * @return string returns the mime type of this content
     */
    public function getContentType()
    {
        return $this->getHeaderLine('Content-Type');
    }

    /**
     * Checks if the Content-Type is of a certain type.  This is useful if the
     * Content-Type header contains charset information and you need to know if
     * the Content-Type matches a particular type.
     *
     * @param string $type Content type to check against
     *
     * @return bool
     */
    public function isContentType($type)
    {
        return stripos($this->getHeaderLine('Content-Type'), $type) !== false;
    }

    /**
     * Get the Date HTTP header.
     *
     * @return string|null returns the date and time that the message was sent
     */
    public function getDate()
    {
        return $this->getHeaderLine('Date');
    }

    /**
     * Get the ETag HTTP header.
     *
     * @return string|null returns an identifier for a specific version of a resource, often a Message digest
     */
    public function getEtag()
    {
        return $this->getHeaderLine('ETag');
    }

    /**
     * Get the Expires HTTP header.
     *
     * @return string|null returns the date/time after which the response is considered stale
     */
    public function getExpires()
    {
        return $this->getHeaderLine('Expires');
    }

    /**
     * Get the Last-Modified HTTP header.
     *
     * @return string|null Returns the last modified date for the requested object, in RFC 2822 format
     *                     (e.g. Tue, 15 Nov 1994 12:45:26 GMT)
     */
    public function getLastModified()
    {
        return $this->getHeaderLine('Last-Modified');
    }

    /**
     * Get the Location HTTP header.
     *
     * @return string|null used in redirection, or when a new resource has been created
     */
    public function getLocation()
    {
        return $this->getHeaderLine('Location');
    }

    /**
     * Get the Pragma HTTP header.
     *
     * @return Header|null returns the implementation-specific headers that may have various effects anywhere along
     *                     the request-response chain
     */
    public function getPragma()
    {
        return $this->getHeaderLine('Pragma');
    }

    /**
     * Get the Proxy-Authenticate HTTP header.
     *
     * @return string|null Authentication to access the proxy (e.g. Basic)
     */
    public function getProxyAuthenticate()
    {
        return $this->getHeaderLine('Proxy-Authenticate');
    }

    /**
     * Get the Retry-After HTTP header.
     *
     * @return int|null if an entity is temporarily unavailable, this instructs the client to try again after a
     *                  specified period of time
     */
    public function getRetryAfter()
    {
        return (int) $this->getHeaderLine('Retry-After');
    }

    /**
     * Get the Server HTTP header.
     *
     * @return string|null A name for the server
     */
    public function getServer()
    {
        return $this->getHeaderLine('Server');
    }

    /**
     * Get the Set-Cookie HTTP header.
     *
     * @return string|null an HTTP cookie
     */
    public function getSetCookie()
    {
        return $this->getHeaderLine('Set-Cookie');
    }

    /**
     * Get the Trailer HTTP header.
     *
     * @return string|null the Trailer general field value indicates that the given set of header fields is present in
     *                     the trailer of a message encoded with chunked transfer-coding
     */
    public function getTrailer()
    {
        return $this->getHeaderLine('Trailer');
    }

    /**
     * Get the Transfer-Encoding HTTP header.
     *
     * @return string|null The form of encoding used to safely transfer the entity to the user
     */
    public function getTransferEncoding()
    {
        return $this->getHeaderLine('Transfer-Encoding');
    }

    /**
     * Get the Vary HTTP header.
     *
     * @return string|null tells downstream proxies how to match future request headers to decide whether the cached
     *                     response can be used rather than requesting a fresh one from the origin server
     */
    // TODO : regarder ici comment c'est fait : https://github.com/symfony/http-foundation/blob/master/Response.php#L1009
    public function getVary()
    {
        return $this->getHeaderLine('Vary');
    }

    /**
     * Get the Via HTTP header.
     *
     * @return string|null informs the client of proxies through which the response was sent
     */
    public function getVia()
    {
        return $this->getHeaderLine('Via');
    }

    /**
     * Get the Warning HTTP header.
     *
     * @return string|null A general warning about possible problems with the entity body
     */
    public function getWarning()
    {
        return $this->getHeaderLine('Warning');
    }

    /**
     * Get the WWW-Authenticate HTTP header.
     *
     * @return string|null Indicates the authentication scheme that should be used to access the requested entity
     */
    public function getWwwAuthenticate()
    {
        return $this->getHeaderLine('WWW-Authenticate');
    }

    //*************************
    // https://github.com/yiisoft/yii2-httpclient/blob/master/src/Response.php#L84
    //*******************************

    /**
     * Returns default format automatically detected from headers and content.
     *
     * @return string|null format name, 'null' - if detection failed
     */
    public function detectFormat()
    {
        $format = $this->detectFormatByHeader();
        if ($format === null) {
            $format = $this->detectFormatByContent((string) $this->getBody());
        }

        return $format;
    }

    /**
     * Detects format from headers.
     *
     * @return null|string format name, 'null' - if detection failed
     */
    private function detectFormatByHeader()
    {
        $contentTypeHeaders = $this->getHeader('Content-Type');
        if (! empty($contentTypeHeaders)) {
            $contentType = end($contentTypeHeaders);
            if (stripos($contentType, 'json') !== false) {
                return self::FORMAT_JSON;
            }
            if (stripos($contentType, 'urlencoded') !== false) {
                return self::FORMAT_URLENCODED;
            }
            if (stripos($contentType, 'xml') !== false) {
                return self::FORMAT_XML;
            }
        }
    }

    /**
     * Detects response format from raw content.
     *
     * @param string $content raw response content
     *
     * @return null|string format name, 'null' - if detection failed
     */
    // TODO : on peut surement faire un middleware pour ajouter un contentType = application/json ou /xml ou html/text selon la détection du format ???? cela semble une bonne idée !!!!
    private function detectFormatByContent($content)
    {
        if (preg_match('/^\\{.*\\}$/is', $content)) {
            return self::FORMAT_JSON;
        }
        if (preg_match('/^([^=&])+=[^=&]+(&[^=&]+=[^=&]+)*$/', $content)) {
            return self::FORMAT_URLENCODED;
        }
        if (preg_match('/^<.*>$/s', $content)) {
            return self::FORMAT_XML;
        }
    }
}
