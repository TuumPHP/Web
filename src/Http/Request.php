<?php
namespace Tuum\Web\Http;

use Closure;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Tuum\Stack\Http\Request as BaseRequest;
use Tuum\Stack\Http\Response;
use Tuum\Stack\StackHandleInterface;
use Tuum\Web\App;

class Request extends BaseRequest
{
    /**
     * @var App
     */
    public $app;

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

        return $request;
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