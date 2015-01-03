<?php
namespace Tuum\Web;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\ServiceInterface\ContainerInterface;
use Tuum\Web\Stack\Stackable;
use Tuum\Web\Stack\StackableInterface;
use Tuum\Web\Stack\StackHandleInterface;
use Tuum\Locator\Container;

class App implements ContainerInterface
{
    const TOKEN_NAME = 'token';
    const FLASH_NAME = 'flash';
    const ROUTE_PARAM = 'params';
    const ROUTE_NAMES = 'namedRoutes';
    const CONTROLLER  = 'controller';

    /**
     * @var Container
     */
    public $container;

    /**
     * @var StackableInterface
     */
    public $stack;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param Container $container
     * @return static
     */
    public static function forge($container)
    {
        return new static($container);
    }

    /**
     * @param string $key
     * @param array  $data
     * @return mixed
     */
    public function __call($key, $data=[])
    {
        return $this->get($key, $data);
    }

    /**
     * @param string $key
     * @param array  $data
     * @return mixed
     */
    public function get($key, $data = [])
    {
        return $this->container->get($key, $data);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->container->set($key,$value);
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  managing instance and stacks
    // +----------------------------------------------------------------------+
    /**
     * @param StackHandleInterface $stack
     * @return StackableInterface
     */
    public function push($stack)
    {
        if ($this->stack) {
            return $this->stack->push($stack);
        }
        $this->stack = Stackable::makeStack($stack);
        return $this->stack;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle($request)
    {
        $request->setApp($this);
        return $this->stack->handle($request);
    }

}