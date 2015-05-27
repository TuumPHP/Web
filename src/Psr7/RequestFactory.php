<?php
namespace Tuum\Web\Psr7;

use Aura\Session\SessionFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;

class RequestFactory extends ServerRequestFactory
{

    /**
     * Create a request from the supplied superglobal values.
     *
     * If any argument is not supplied, the corresponding superglobal value will
     * be used.
     *
     * The ServerRequest created is then passed to the fromServer() method in
     * order to marshal the request URI and headers.
     *
     * @see fromServer()
     * @param array $globals  $GLOBALS super-global
     * @return Request
     */
    public static function fromGlobalData(
        array $globals = []
    ) {
        $server  = self::normalizeServer(self::arrayGet($globals, '_SERVER', $_SERVER));
        $files   = self::arrayGet($globals, '_FILES', $_FILES);
        $cookies = self::arrayGet($globals, '_COOKIE', $_COOKIE);
        $query   = self::arrayGet($globals, '_GET', $_GET);
        $body    = self::arrayGet($globals, '_POST', $_POST);
        $headers = self::marshalHeaders($server);
        $request = new Request(
            $server,
            $files,
            self::marshalUriFromServer($server, $headers),
            self::get('REQUEST_METHOD', $server, 'GET'),
            'php://input',
            $headers
        );

        return $request
            ->withSession((new SessionFactory)->newInstance($cookies))
            ->withCookieParams($cookies)
            ->withQueryParams($query)
            ->withParsedBody($body);
    }

    /**
     * @param array $array
     * @param string $key
     * @param array  $default
     * @return array
     */
    private static function arrayGet($array, $key, $default=[])
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
    
    /**
     * @param string $path
     * @param string $method
     * @return Request
     */
    public static function fromPath(
        $path,
        $method = 'GET'
    ) {
        $request = new Request(
            [],
            [],
            new Uri($path),
            $method ?: 'GET',
            'php://input',
            []
        );
        return $request;
    }
}