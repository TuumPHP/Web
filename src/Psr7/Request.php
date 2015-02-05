<?php
namespace Tuum\Web\Psr7;

use Phly\Http\ServerRequest;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Web;

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
    /**
     * @var Respond
     */
    protected $respond;

    /**
     * @var Web
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
     * @param array  $serverParams
     * @param array  $fileParams
     * @param null   $uri
     * @param null   $method
     * @param string $body
     * @param array  $headers
     */
    public function __construct(
        array $serverParams = [],
        array $fileParams = [],
        $uri = null,
        $method = null,
        $body = 'php://input',
        array $headers = []
    ) {
        $this->respond = new Respond();
        parent::__construct($serverParams, $fileParams, $uri, $method, $body, $headers);
    }

    /**
     * @param Web $web
     */
    public function setWebApp($web)
    {
        $this->web = $web;
    }

    /**
     * @return Web
     */
    public function getWebApp()
    {
        return $this->web;
    }

    /**
     * @param string|ApplicationInterface $filter
     * @return null|Response
     */
    public function filter($filter)
    {
        if (is_string($filter)) {
            $filter = $this->web->get($filter);
        }
        if ($filter instanceof \Closure) {
            return $filter($this);
        }
        return null;
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
     * @param string $base
     * @param string $path
     * @return Request
     */
    public function withPathToMatch($base, $path=null)
    {
        $new = clone $this;
        if(!$path) {
            $path = $base;
            $base = '';
        }
        $new->base_path     = $base;
        $new->path_to_match = $path;
        return $new;
    }
    
    /**
     * @return string
     */
    public function getPathToMatch()
    {
        if(is_null($this->path_to_match)) {
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
     * @param string|array $key
     * @param mixed|null   $value
     * @return $this
     */
    public function respondWith($key, $value=null)
    {
        $this->respond = $this->respond->with($key, $value);
        return $this;
    }

    /**
     * @return Respond
     */
    public function respond()
    {
        $this->respond->setRequest($this);
        return $this->respond;
    }
}