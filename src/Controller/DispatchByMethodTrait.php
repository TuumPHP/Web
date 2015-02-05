<?php
namespace Tuum\Web\Controller;

use Tuum\Web\App;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

/**
 * Class DispatchByMethodTrait
 * @package Tuum\Controller
 *
 * Dispatching based on request method.
 */
trait DispatchByMethodTrait
{
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
        /*
         * set up request information
         */
        $params = (array)$request->getQueryParams();
        $method = $request->getMethod();
        $method = 'on' . ucwords( $method );
        if ( !method_exists( $this, $method ) ) {
            return null;
        }
        /*
         * invoke based on the method name i.e. onMethod(...)
         * also setup arguments from route parameters and get query.
         */
        return $this->dispatchMethod($method, $params);
    }

}