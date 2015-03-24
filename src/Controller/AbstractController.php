<?php
namespace Tuum\Web\Controller;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Respond;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Psr7\StreamFactory;

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

        if (strtoupper($request->getMethod()) === 'HEAD') {
            return $this->onHead($request);
        }
        return $this->dispatch($request);
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    private function onHead($request)
    {
        $request  = $request->withMethod('GET');
        $response = $this->dispatch($request);
        if ($response) {
            return $response->withBody(StreamFactory::string(''));
        }
        return null;
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
