<?php
namespace Tuum\Web\Controller;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Respond;
use Tuum\Web\Psr7\Response;

abstract class AbstractController implements ApplicationInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Respond
     */
    protected $respond;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @param Request $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        $this->request  = $request;
        $this->basePath = $request->getBasePath();
        $this->respond  = $request->respond()->with('basePath', $this->basePath);

        return $this->dispatch($request);
    }

    /**
     * @param Request $request
     * @return Response|null;
     */
    abstract protected function dispatch($request);
    
    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    protected function dispatchMethod($method, $params)
    {
        $refMethod = new \ReflectionMethod($this, $method);
        $refArgs   = $refMethod->getParameters();
        $arguments = array();
        foreach ($refArgs as $arg) {
            $key             = $arg->getPosition();
            $name            = $arg->getName();
            $opt             = $arg->isOptional() ? $arg->getDefaultValue() : null;
            $val             = isset($params[$name]) ? $params[$name] : $opt;
            $arguments[$key] = $val;
        }
        $refMethod->setAccessible(true);
        return $refMethod->invokeArgs($this, $arguments);
    }

}
