<?php
namespace Tuum\Web\Stack;

use Aura\Router\Route;
use Closure;
use Tuum\Locator\Container;
use Tuum\Locator\Locator;
use Tuum\Stack\Http\Request;
use Tuum\Stack\Http\Response;
use Tuum\Stack\StackHandleInterface;
use Tuum\Web\App;

class Dispatcher implements StackHandleInterface
{
    /**
     * @var Container
     */
    public $container;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function handle($request)
    {
        /** @var Route $route */
        $route = $request->attributes->get(App::CONTROLLER);
        $next  = $route->name;

        if (is_string($next)) {
            return $this->dispatchClass($request, $next);
        }
        if ($next instanceof StackHandleInterface) {
            return $next->handle($request);
        }
        if ($next instanceof Closure) {
            return $next($request);
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @param Request $request
     * @param string  $class
     * @return null|Response
     */
    protected function dispatchClass($request, $class)
    {
        $method = null;
        if (strpos($class, '@') !== false) {
            list($class, $method) = explode('@', $class);
        }
        $next = $this->findObject($class);
        if (isset($method)) {
            return $this->dispatchMethod($request, $next, $method);
        }
        if (method_exists($next, 'matchBy')) {
            return $this->matchBy($request, $next, $next->matchBy());
        }
        if ($next instanceof StackHandleInterface) {
            return $next->handle($request);
        }
        if ($next instanceof Closure) {
            return $next($request);
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @param string $class
     * @return object|null
     */
    protected function findObject($class)
    {
        if ($this->container && $this->container->exists($class)) {
            return $this->container->evaluate($class, ['container' => $this->container]);
        }
        if (method_exists($class, 'forge')) {
            return $class::forge(['container' => $this->container]);
        }
        return new $class;
    }

    /**
     * @param Request $request
     * @param object  $next
     * @param string  $method
     * @return null|Response
     */
    protected function dispatchMethod($request, $next, $method)
    {
        $args = $request->attributes->get(App::ROUTE_PARAM);
        return call_user_func_array([$next, $method], $args);
    }

    /**
     * @param Request $request
     * @param object  $next
     * @param array   $matchBy
     */
    protected function matchBy($request, $next, $matchBy)
    {
        foreach($matchBy as $pattern) {

        }
    }
}
