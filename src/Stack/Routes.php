<?php
namespace Tuum\Web\Stack;

use Aura\Router\Route;
use Aura\Router\RouterFactory;
use Aura\Router\Router as AuraRouter;
use Tuum\Stack\Http\Request;
use Tuum\Stack\Http\Response;
use Tuum\Stack\StackableTrait;
use Tuum\Stack\StackHandleInterface;
use Tuum\Web\App;

class Routes implements StackHandleInterface
{
    use StackFilterTrait;

    /**
     * @var AuraRouter
     */
    public $router;

    /**
     * @param AuraRouter $router
     */
    public function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * @param AuraRouter $router
     * @return Routes
     */
    public static function forge($router)
    {
        $self = new self($router);
        return $self;
    }

    /**
     * @return AuraRouter
     */
    public static function routes()
    {
        $factory = new RouterFactory();
        return $factory->newInstance();
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function handle($request)
    {
        $path   = $request->getPathInfo();
        $server = $request->server->all();
        $route  = $this->router->match($path, $server);
        if (!$route) {
            return null;
        }
        $path = $route->matches[0];
        $request->attributes->set(App::CONTROLLER, $route);
        $request->attributes->set(App::ROUTE_NAMES, $this->router);
        $request->attributes->set(App::ROUTE_PARAM, $route->params);
        if ($response = $this->applyFilters($request)) {
            return $response;
        }
        return $this->dispatch($request, $route);
    }

    /**
     * @param Request $request
     * @param Route   $route
     * @return null|Response
     */
    private function dispatch($request, $route)
    {
        $class = $route->name;

        if (method_exists($class, 'forge')) {
            $next = $class::forge();
        } else {
            $next = new $class;
        }
        $request->attributes->set(App::ROUTE_PARAM, $route->params);
        if ($next instanceof StackHandleInterface) {
            return $next->handle($request);
        }
        if ($next instanceof \Closure) {
            return $next($request);
        }
        throw new \InvalidArgumentException();
    }
}
