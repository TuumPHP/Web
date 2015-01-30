<?php
namespace Tuum\Web\Psr7;

use Phly\Http\ServerRequest;
use Tuum\Web\ApplicationInterface;
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
        $this->respond = new Respond($this);
        return parent::__construct($serverParams, $fileParams, $uri, $method, $body, $headers);
    }

    /**
     * @param Web $web
     */
    public function setWebApp($web)
    {
        $this->web = $web;
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
     * @param string $path
     * @return Request
     */
    public function withPathToMatch($path)
    {
        $new = clone $this;
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
        return $this->respond;
    }
}