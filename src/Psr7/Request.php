<?php
namespace Tuum\Web\Psr7;

use Aura\Session\Session;
use Phly\Http\ServerRequest;
use Psr\Http\Message\UriInterface;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Application;
use Tuum\Web\View\Value;

/**
 * Class Request
 *
 * a Tuum's Request class.
 *
 * contains a mutable $respond object as a decorator for creating a response.
 *
 * @package Tuum\Web\Psr7
 */
class Request extends ServerRequest
{
    const SESSION_MGR = 'session.mgr';

    /**
     * @var Respond
     */
    protected $respond;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var Application
     */
    protected $web;

    /**
     * @var string
     */
    protected $path_to_match = null;

    /**
     * @var string
     */
    protected $base_path = '';

    /**
     * @var Session
     */
    private $session;

    /**
     * @param array             $serverParams
     * @param array             $fileParams
     * @param UriInterface|null $uri
     * @param string|null       $method
     * @param string            $body
     * @param array             $headers
     */
    public function __construct(
        array $serverParams = [],
        array $fileParams = [],
        $uri = null,
        $method = null,
        $body = 'php://input',
        array $headers = []
    ) {
        $this->respond  = new Respond();
        $this->redirect = new Redirect();
        parent::__construct($serverParams, $fileParams, $uri, $method, $body, $headers);
    }

    /**
     * @param Application $web
     */
    public function setWebApp($web)
    {
        $this->web = $web;
    }

    /**
     * @return Application
     */
    public function getWebApp()
    {
        return $this->web;
    }

    /**
     * @param string|mixed $filter
     * @return MiddlewareInterface|ApplicationInterface
     */
    public function getFilter($filter)
    {
        if (is_string($filter)) {
            $filter = $this->web->get($filter);
        }
        return $filter;
    }

    /**
     * @param array $attributes
     * @return Request
     */
    public function withAttributes(array $attributes)
    {
        $new = clone $this;
        foreach ($attributes as $key => $val) {
            $new = $new->withAttribute($key, $val);
        }
        return $new;
    }

    /**
     * @param string $base
     * @param string $path
     * @return Request
     */
    public function withPathToMatch($base, $path)
    {
        $new                = clone $this;
        $new->base_path     = $base;
        $new->path_to_match = $path;
        return $new;
    }

    /**
     * @return string
     */
    public function getPathToMatch()
    {
        if (is_null($this->path_to_match)) {
            return $this->getUri()->getPath();
        }
        return $this->path_to_match;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * return cloned $respond object.
     * the $request object will not be altered.
     *
     * use this to construct a new Response.
     * sets $request in the respond.
     *
     * @return Respond
     */
    public function respond()
    {
        $respond = $this->respond->withRequest($this);
        $respond->with($this->getAttributes());
        return $respond;
    }

    /**
     * @param array $list
     * @return Redirect
     */
    public function redirect($list = [])
    {
        $data = (array)$this->getAttributes();
        $list = array_merge($list, [Value::INPUTS, Value::ERRORS, Value::MESSAGE]);
        foreach ($data as $key => $v) {
            if (!in_array($key, $list)) {
                unset($data[$key]);
            }
        }
        $respond = $this->redirect->withRequest($this);
        $respond->with($data);
        return $respond;
    }

    /**
     * @param Session $session
     * @return $this
     */
    public function withSession($session)
    {
        $new          = clone $this;
        $new->session = $session;
        return $new;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}