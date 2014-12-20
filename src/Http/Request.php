<?php
namespace Tuum\Web\Http;

use Closure;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Tuum\Web\Stack\StackHandleInterface;
use Tuum\Web\App;

class Request extends BaseRequest
{
    /**
     * @var App
     */
    public $app;

    /**
     * @var Respond
     */
    protected $respond;

    /**
     * a sample for starting a new Request based on super globals.
     * specify session storage if necessary.
     *
     * @param SessionStorageInterface $storage
     * @return Request
     */
    public static function startGlobal(SessionStorageInterface $storage = null)
    {
        $request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);

        // set up session
        $session = new Session($storage);
        $request->setSession($session);
        $request->setRespond(new Respond($request));

        return $request;
    }

    /**
     * @param string $path
     * @param null|SessionStorageInterface $storage
     * @return Request
     */
    public static function startPath($path, SessionStorageInterface $storage=null)
    {
        $server = [];
        $server['PHP_SELF'] = $server['SCRIPT_NAME'] = $server['SCRIPT_FILENAME'] = $path;
        $request = new Request([], [], [], [], [], $server);

        // set up session
        $session = new Session($storage);
        $request->setSession($session);
        $request->setRespond(new Respond($request));

        return $request;
    }

    /**
     * @param string $path
     * @return Request
     */
    public function createNewRequest($path)
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
        $server  = $this->server;
        $newPath = $this->getBaseUrl() . $path;
        $server->set('SCRIPT_FILENAME', $newPath);
        $server->set('SCRIPT_NAME', $newPath);
        $server->set('PHP_SELF', $newPath);
        $this->attributes->set('url.mapped', $newPath);
    }
    
    /**
     * @param Respond $respond
     */
    protected function setRespond($respond)
    {
        $this->respond = $respond;
    }

    /**
     * @return Respond
     */
    public function respond()
    {
        return $this->respond;
    }
    
    /**
     * @param string                       $name
     * @param Closure|StackHandleInterface $filter
     * @return $this
     */
    public function setFilter($name, $filter)
    {
        $this->app->container->set($name, $filter);
        return $this;
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
        if ($filter instanceof StackHandleInterface) {
            return $filter->handle($this);
        }
        if ($filter instanceof Closure) {
            return $filter($this);
        }
        return null;
    }

}