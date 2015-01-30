<?php
namespace Tuum\Web\Psr7;

use Phly\Http\ServerRequestFactory;
use Phly\Http\Uri;

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
     * @param array $server $_SERVER superglobal
     * @param array $query $_GET superglobal
     * @param array $body $_POST superglobal
     * @param array $cookies $_COOKIE superglobal
     * @param array $files $_FILES superglobal
     * @return Request
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ) {
        $server  = self::normalizeServer($server ?: $_SERVER);
        $files   = $files   ?: $_FILES;
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
            ->withCookieParams($cookies ?: $_COOKIE)
            ->withQueryParams($query ?: $_GET)
            ->withBodyParams($body ?: $_POST);
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