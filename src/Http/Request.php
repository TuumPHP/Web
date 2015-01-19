<?php
namespace Tuum\Web\Http;

use Closure;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Tuum\Router\RouteNamesInterface;
use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\App;

class Request extends BaseRequest
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Respond
     */
    protected $respond;

    /**
     * @var Redirect
     */
    protected $redirect;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var RouteNamesInterface
     */
    protected $named;

    /**
     * a sample for starting a new Request based on super globals.
     * specify session storage if necessary.
     *
     * @param SessionStorageInterface $storage
     * @return Request
     */
    public static function startGlobal(SessionStorageInterface $storage = null)
    {
        $request = RequestFactory::createWithGlobal($storage);
        RequestFactory::setup($request);
        return $request;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array  $server
     * @return Request
     */
    public static function startPath($path, $method='GET', $server=[])
    {
        $request = RequestFactory::createWithPath($path, $method, $server);
        RequestFactory::setup($request);
        return $request;
    }

    /**
     * @param string $path
     * @return Request
     */
    public function duplicateWithNewPath($path)
    {
        $newPath    = $this->getBaseUrl() . $path;
        $server     = $this->server->all();
        $attributes = $this->attributes->all();
        // update with new values
        $server['PHP_SELF']       = $server['SCRIPT_NAME'] = $server['SCRIPT_FILENAME'] = $newPath;
        $attributes['url.mapped'] = $newPath;
        return $this->duplicate(null, null, $attributes, null, null, $server);
    }

    /**
     * @param string $path
     */
    public function updatePath($path)
    {
        $newBase = $this->getBaseUrl() . $path;
        $newPath = substr($this->pathInfo, strlen($path));

        $server  = $this->server;
        $server->set('SCRIPT_FILENAME', $newBase);
        $server->set('SCRIPT_NAME', $newBase);
        $server->set('PHP_SELF', $newBase);
        $this->basePath = $newBase;
        $this->pathInfo = $newPath;
        $this->attributes->set('url.mapped', $newBase);
    }
    
    /**
     * @return Respond
     */
    public function respond()
    {
        $this->respond->setRequest($this);
        return $this->respond;
    }

    /**
     * @return Redirect
     */
    public function redirect()
    {
        $this->redirect->setRequest($this);
        return $this->redirect;
    }

    /**
     * @param App $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }
    
    /**
     * @param string $name
     * @return null|Response
     */
    public function filter($name)
    {
        if (!$filter = $this->app->get($name)) {
            return null;
        }
        if ($filter instanceof AppHandleInterface) {
            return $filter->__invoke($this);
        }
        if ($filter instanceof Closure) {
            return $filter($this);
        }
        return null;
    }

    /**
     * @return UrlGenerator
     */
    public function url()
    {
        $url = clone $this->url;
        $url->setRequest($this);
        return $url;
    }

    /**
     * @param RouteNamesInterface $named
     */
    public function setNamedRoute($named)
    {
        $this->named = $named;
    }

    /**
     * @param string $name
     * @param array  $arg
     * @return string
     */
    public function named($name, $arg=[])
    {
        if (!$this->named) {
            throw new \BadMethodCallException('no named routes');
        }
        return $this->named->get($name, $arg);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        return $this->attributes->get($name);
    }

    /**
     * @param string $name
     * @param mixed  $data
     * @return $this
     */    
    public function setAttribute($name, $data)
    {
        $this->attributes->set($name, $data);
        return $this;
    }
}