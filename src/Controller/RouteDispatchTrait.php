<?php
namespace Tuum\Web\Controller;

use Tuum\Routing\Matcher;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

trait RouteDispatchTrait
{
    /**
     * @return array
     */
    abstract protected function getRoutes();

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    abstract protected function dispatchMethod($method, $params);

    /**
     * @param Request $request
     * @return Response|null;
     */
    protected function dispatch($request)
    {
        $method = $request->getMethod();
        $path   = $request->getPathToMatch();
        $routes = $this->getRoutes();
        foreach ($routes as $pattern => $dispatch) {
            $params = Matcher::verify($pattern, $path, $method);
            if ($params) {
                $params += $request->getQueryParams() ?: [];
                $method  = 'on'.ucwords($dispatch);
                return $this->dispatchMethod($method, $params);
            }
        }
        return null;
    }
}
