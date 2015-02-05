<?php
namespace Tuum\Web\Stack;

use Tuum\Router\RouterInterface;
use Tuum\Web\App;
use Tuum\Web\Middleware\MatchRootTrait;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class RouterStack implements MiddlewareInterface
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
    public function __construct($router, $dispatcher)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
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
        /*
         * execute the dispatcher and filters using blank new web application.
         */
        $app = $request->getWebApp()->cloneApp();
        $this->dispatcher->setRoute($route);
        $app->prepend($this->dispatcher);

        if ($beforeFilters = $route->before()) {
            foreach($beforeFilters as $filter) {
                $filter = $request->getFilter($filter);
                $app->prepend($filter);
            }
        }
        if($route->trailing()) {
            $request = $request->withPathToMatch($route->matched(), $route->trailing());
        }
        $request = $request->withAttribute(App::ROUTE_NAMES, $this->router->getReverseRoute($request));
        return $app($request);
    }
}
