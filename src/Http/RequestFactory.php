<?php
namespace Tuum\Web\Http;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class RequestFactory
{

    /**
     * a sample for starting a new Request based on super globals.
     * specify session storage if necessary.
     *
     * @param SessionStorageInterface $storage
     * @return Request
     */
    public static function createWithGlobal(SessionStorageInterface $storage = null)
    {
        $request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);

        // set up session
        $session = new Session($storage);
        $request->setSession($session);

        return $request;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array  $server
     * @return Request
     */
    public static function createWithPath($path, $method = 'GET', $server = [])
    {
        $server = array_replace(array(
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_USER_AGENT'      => 'Symfony/2.X',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR'          => '127.0.0.1',
            'SCRIPT_NAME'          => '',
            'SCRIPT_FILENAME'      => '',
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_TIME'         => time(),
        ), $server);

        $server['PATH_INFO']      = '';
        $server['REQUEST_METHOD'] = strtoupper($method);

        $components = parse_url($path);
        if (isset($components['host'])) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST']   = $components['host'];
        }

        if (isset($components['scheme'])) {
            if ('https' === $components['scheme']) {
                $server['HTTPS']       = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }

        if (isset($components['port'])) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST']   = $server['HTTP_HOST'] . ':' . $components['port'];
        }

        if (isset($components['user'])) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }

        if (isset($components['pass'])) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }

        if (!isset($components['path'])) {
            $components['path'] = '/';
        }

        $query       = array();
        $queryString = '';
        if (isset($components['query'])) {
            parse_str(html_entity_decode($components['query']), $qs);

            if ($query) {
                $query       = array_replace($qs, $query);
                $queryString = http_build_query($query, '', '&');
            } else {
                $query       = $qs;
                $queryString = $components['query'];
            }
        } elseif ($query) {
            $queryString = http_build_query($query, '', '&');
        }

        $server['REQUEST_URI']  = $components['path'] . ('' !== $queryString ? '?' . $queryString : '');
        $server['QUERY_STRING'] = $queryString;

        $request = new Request($query, [], [], [], [], $server);

        $request->setRespond(new Respond());

        return $request;
    }

    /**
     * @param Request $request
     */
    public static function setup($request)
    {
        $request->setRespond(new Respond());
        $request->setUrl(new UrlGenerator());
    }
}