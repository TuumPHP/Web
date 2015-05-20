<?php
namespace Tuum\Web\Stack;

use Closure;
use Tuum\Router\Route;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\Middleware\AfterReleaseTrait;
use Tuum\Web\Middleware\BeforeFilterTrait;
use Tuum\Web\Middleware\Middleware;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\Middleware\ReturnRequest;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Application;

class Dispatcher implements ApplicationInterface
{
    use MiddlewareTrait;
    
    use BeforeFilterTrait;
    
    use AfterReleaseTrait;
    
    /**
     * @var Route
     */
    protected $route;

    /**
     * @var Application
     */
    protected $dic;

    /**
     * @param null|Application $dic
     */
    public function __construct($dic = null)
    {
        $this->dic = $dic;
    }

    /**
     * @param Request       $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        // apply before filter. 
        list($request, $response) = $this->filterBefore($request);
        if ($response) {
            return $response;
        }

        // dispatch the controller/closure!
        $response = $this->dispatch($request);

        // apply after release. 
        return $this->applyAfterReleases($request, $response);
    }

    /**
     * @param Route $route
     * @return Dispatcher
     */
    public function withRoute($route)
    {
        $dispatch = clone($this);
        $dispatch->prepare($route);
        return $dispatch;
    }

    /**
     * prepares before filters and after releases from route.
     * 
     * @param Route $route
     */
    private function prepare($route)
    {
        $this->route = $route;
        $this->setBeforeFilter((array) $route->before());
        $this->setAfterRelease((array) $route->after());
    }

    /**
     * @param $request
     * @return null|Response
     */
    private function dispatch($request)
    {
        $class = $this->route->handle();
        // prepare object to dispatch.
        if (is_string($class)) {
            $next = $this->dic->get($class);
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
}
