<?php
namespace Tuum\Web\Controller;

use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Psr7\StreamFactory;

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
        if (strtoupper($method) === 'OPTIONS') {
            return $this->onOptions();
        }
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

    /**
     * @return Response
     */
    private function onOptions()
    {
        $refClass = new \ReflectionObject($this);
        $methods  = $refClass->getMethods();
        $options  = ['OPTIONS'];
        foreach($methods as $method ) {
            if(preg_match('/on([_a-zA-Z0-9]+)/', $method->getName(), $match)) {
                $options[] = strtoupper($match[1]);
            }
        }
        $options = array_unique($options);
        sort($options);
        $list = implode(',', $options);
        return new Response(StreamFactory::string(''), 200, ['Allow'=>$list]);
    }

}