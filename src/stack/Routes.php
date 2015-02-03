<?php
namespace Tuum\Web\Stack;

use Tuum\Router\Dispatcher;
use Tuum\Router\RouterInterface;
use Tuum\Web\App;
use Tuum\Web\Middleware\MatchRootTrait;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class Routes implements MiddlewareInterface
{
    use MiddlewareTrait;
    
    use MatchRootTrait;
    
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
    protected function __construct($router, $dispatcher)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param RouterInterface    $router
     * @param Dispatcher         $dispatcher
     * @return Routes
     */
    public static function forge($router, $dispatcher=null)
    {
        $dispatcher = $dispatcher ?: new Dispatcher();
        $self = new self($router, $dispatcher);
        return $self;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        if (!$this->router || 
            !$this->isMatch($request)) {
            return $this->execNext($request);
        }
        $route  = $this->router->match($request);
        if (!$route) {
            return $this->execNext($request);
        }
        if ($beforeFilters = $route->before()) {
            foreach($beforeFilters as $filter) {
                $filter = $request->getFilter($filter);
                $this->prepend($filter);
            }
        }
        $request = $request->withAttribute(App::ROUTE_NAMES, $this->router->getReverseRoute($request));
        return $this->dispatcher->__invoke($request, $route);
    }
}
