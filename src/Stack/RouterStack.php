<?php
namespace Tuum\Web\Stack;

use Tuum\Router\Route;
use Tuum\Router\RouterInterface;
use Tuum\Router\RouteCollector;
use Tuum\Web\Middleware\AfterReleaseTrait;
use Tuum\Web\Middleware\BeforeFilterTrait;
use Tuum\Web\Middleware\MatchRootTrait;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class RouterStack implements MiddlewareInterface
{
    use MiddlewareTrait;

    use MatchRootTrait;

    use BeforeFilterTrait;
    
    use AfterReleaseTrait;

    /**
     * @var RouterInterface
     */
    public $router;

    /**
     * @var Dispatcher
     */
    public $dispatcher;

    /**
     * @param RouterInterface $router
     * @param Dispatcher      $dispatcher
     */
    public function __construct($router, $dispatcher)
    {
        $this->router     = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return RouterStack
     */
    public function forge()
    {
        $new = clone($this);
        $new->router = clone($this->router);
        return $new;
    }

    /**
     * @return mixed|RouteCollector
     */
    public function getRouting()
    {
        return $this->router->getRouting();
    }

    /**
     * @param Request       $request
     * @param null|Response $response
     * @param null|\Closure $next
     * @return null|Response
     * @throws \ErrorException
     */
    public function __invoke($request, $response = null, $next = null)
    {
        /** @var Request $request */
        if (!$this->router) {
            throw new \ErrorException('no router for routing.');
        }
        // matches requested path with the root.
        if (!$this->matchRoot($request)) {
            return $this->next ? $this->next->__invoke($request) : null;
        }

        // apply before filter. 
        list($request, $response) = $this->filterBefore($request);
        if ($response) {
            return $response;
        }

        // match and dispatch!
        $response = $this->match($request);

        // apply after release.
        return $this->applyAfterReleases($request, $response);
    }

    /**
     * matches the route!
     *
     * @param Request $request
     * @return null|Response
     */
    private function match($request)
    {
        $route = $this->router->match(
            $request->getUri()->getPath(),
            $request->getMethod()
        );
        if (!$route) {
            // not matched. dispatch the next middleware.
            return $this->next ? $this->next->__invoke($request) : null;
        }
        return $this->dispatch($request, $route);
    }

    /**
     * execute the dispatcher and filters using blank new web application.
     *
     * @param Request $request
     * @param Route   $route
     * @return mixed
     */
    private function dispatch($request, $route)
    {
        if ($route->matched()) {
            $request = $request->withPathToMatch($route->matched(), $route->trailing());
        }
        // dispatch the route!
        return $this->dispatcher->withRoute($route)->__invoke($request);
    }
}
