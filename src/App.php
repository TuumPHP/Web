<?php
namespace Tuum\Web;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\ServiceInterface\ContainerInterface;
use Tuum\Web\ServiceInterface\RendererInterface;
use Tuum\Web\Stack\Stackable;
use Tuum\Web\Stack\StackableInterface;
use Tuum\Web\Stack\StackHandleInterface;
use Tuum\Locator\Container;

class App implements ContainerInterface
{
    const VIEW_DATA = 'data';
    const TOKEN_NAME = 'token';
    const FLASH_NAME = 'flash';
    const ROUTE_PARAM = 'params';
    const ROUTE_NAMES = 'namedRoutes';
    const CONTROLLER  = 'controller';
    const RENDER_ENGINE = 'view.engine';
    
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

    /**
     * @param RendererInterface|string $engine
     * @return App
     */
    public function setRenderer($engine)
    {
        return $this->set(App::RENDER_ENGINE, $engine);
    }

    /**
     * @return RendererInterface
     */
    public function getRenderer()
    {
        $engine = $this->get(App::RENDER_ENGINE);
        if( is_string($engine)) {
            return $this->get($engine);
        }
        return $engine;
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