<?php
namespace Tuum\Web\Controller;

use Tuum\Routing\Matcher;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Psr7\StreamFactory;

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
        if (strtoupper($method) === 'HEAD') {
            $response = $this->dispatchRoute($request, $path, 'GET');
            if ($response) {
                return $response->withBody(StreamFactory::string(''));
            }
            return null;
        }
        if (strtoupper($method) === 'OPTIONS') {
            return $this->onOptions($path);
        }

        return $this->dispatchRoute($request, $path, $method);
    }

    /**
     * @param string $path
     * @return Response
     */
    private function onOptions($path)
    {
        $routes = $this->getRoutes();
        $options = ['OPTIONS', 'HEAD'];
        foreach($routes as $pattern => $dispatch) {
            if($params = Matcher::verify($pattern, $path, '*')) {
                if(isset($params['method']) && $params['method'] && $params['method']!=='*' ) {
                    $options[] = strtoupper($params['method']);
                }
            }
        }
        $options = array_unique($options);
        sort($options);
        $list = implode(',', $options);
        return new Response(StreamFactory::string(''), 200, ['Allow'=>$list]);
    }

    /**
     * @param Request $request
     * @param string  $path
     * @param string  $method
     * @return Response|null
     */
    private function dispatchRoute($request, $path, $method)
    {
        $routes = $this->getRoutes();
        foreach ($routes as $pattern => $dispatch) {
            $params = Matcher::verify($pattern, $path, $method);
            if ($params) {
                $params += $request->getQueryParams() ?: [];
                $method = 'on' . ucwords($dispatch);
                return $this->dispatchMethod($method, $params);
            }
        }
        return null;
    }
}
