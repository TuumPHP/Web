<?php
namespace Tuum\Web\Stack;

use Closure;
use Tuum\Router\Route;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Web;
use Tuum\Web\Application;

class Dispatcher implements ApplicationInterface
{
    /**
     * @var Route
     */
    protected $route;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param null|Application $app
     */
    public function __construct($app=null)
    {
        $this->app = $app;
    }
    
    /**
     * @param Request $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        $class = $this->route->handle();

        // prepare object to dispatch.
        if (is_string($class) ) {
            $next = $this->app->get($class);
        } elseif (is_callable($class)) {
            $next = $class;
        } else {
            throw new \InvalidArgumentException('no such handler to dispatch');
        }
        // dispatch the next object.
        if ($next instanceof ApplicationInterface) {
            return $next->__invoke($request);
        }
        if ($next instanceof \Closure) {
            return $next($request);
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @param Route $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }
}
