<?php
namespace Tuum\Web\Stack;

use Tuum\Router\RouterInterface;
use Tuum\Routing\RouteCollector;
use Tuum\Web\Middleware\BeforeFilterTrait;
use Tuum\Web\Web;
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
    
    /**
     * @var RouterInterface
     */
    public $router;

    /**
     * @var Dispatcher
     */
    public $dispatcher;

    /**
     * @param RouterInterface    $router
     * @param Dispatcher         $dispatcher
     */
    public function __construct($router, $dispatcher)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
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
     * @param callable|null $next
     * @return null|Response
     * @throws \ErrorException
     */
    public function __invoke($request, $next=null)
    {
        if (!$this->router) {
            throw new \ErrorException('no router for routing.');
        }
        if (!$matched = $this->isMatch($request)) {
            return $this->execNext($request);
        }
        if (isset($matched['matched'])) {
            $request = $request->withPathToMatch($matched['matched'], $matched['trailing']);
        }
        $route  = $this->router->match($request->getUri()->getPath(), $request->getMethod());
        if (!$route) {
            return $this->execNext($request);
        }
        /*
         * execute the dispatcher and filters using blank new web application.
         */
        $app = $request->getWebApp()->cloneApp();
        $this->dispatcher->setRoute($route);
        $app->prepend($this->dispatcher);

        /** @var Request $request ...$request lost track of its type, some how... */
        if (!empty($this->_beforeFilters)) {
            foreach($this->_beforeFilters as $filter) {
                $filter = $request->getFilter($filter);
                $app->prepend($filter);
            }
        }
        if ($beforeFilters = $route->before()) {
            foreach($beforeFilters as $filter) {
                $filter = $request->getFilter($filter);
                $app->prepend($filter);
            }
        }
        if($route->matched()) {
            $request = $request->withPathToMatch($route->matched(), $route->trailing());
        }
        $request = $request->withAttribute(Web::ROUTE_NAMES, $this->router->getReverseRoute($request));
        return $app($request);
    }
}
