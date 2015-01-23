<?php
namespace Tuum\Web;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Tuum\Locator\Container;
use Tuum\Locator\Locator;
use Tuum\View\ViewEngineInterface;
use Tuum\Web\App\AppMarkerInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\ServiceInterface\ContainerInterface;
use Tuum\Web\Stack\Stackable;
use Tuum\Web\Stack\StackableInterface;
use Tuum\Web\App\AppHandleInterface;

/**
 * Class App
 *
 * @package Tuum\Web
 *          web application.
 *
 *
 *          
 */
class App implements ContainerInterface, AppHandleInterface
{
    /*
     * directories
     */
    const ROUTES_FILE  = 'file.routes';
    const CONFIG_DIR   = 'dir.config';
    const TEMPLATE_DIR = 'dir.view';
    const RESOURCE_DIR = 'dir.resource';
    const VAR_DATA_DIR = 'dir.variable';

    const DEBUG = 'debug';
    const LOGGER = 'logger';
    const VIEW_DATA = 'data';
    const TOKEN_NAME = 'token';
    const FLASH_NAME = 'flash';
    const ROUTE_PARAM = 'params';
    const ROUTE_NAMES = 'namedRoutes';
    const CONTROLLER  = 'controller';
    const RENDER_ENGINE = 'renderer';
    const ROUTER = 'router';

    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var StackableInterface
     */
    public $stack;

    /**
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function forge($container=null)
    {
        $container = $container ?: new Container(new Locator());
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
        $data['app'] = $this;
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
     * @param string $key
     * @param array  $data
     * @return $this
     */
    public function share($key, $data = [])
    {
        $data['app'] = $this;
        $this->container->share($key, $data);
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  services
    // +----------------------------------------------------------------------+
    /**
     * @param mixed $message
     */
    public function log($message)
    {
        /** @var LoggerInterface $log */
        $log = $this->get(App::LOGGER);
        if( !$log ) return;
        $log->debug($message);
    }

    /**
     * @param mixed  $message
     * @param string $level
     */
    public function logError($message, $level=LogLevel::ERROR)
    {
        /** @var LoggerInterface $log */
        $log = $this->get(App::LOGGER);
        if( !$log ) return;
        $log->log($level, $message);
    }

    // +----------------------------------------------------------------------+
    //  managing instance and stacks
    // +----------------------------------------------------------------------+
    /**
     * @param AppMarkerInterface $stack
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
     * @param Request  $request
     * @return Response
     */
    public function __invoke($request)
    {
        $request->setApp($this);
        if(!$this->stack) {
            return $request->respond()->notFound();
        }
        $this->log('stack start for '.$request->getMethod().':'.$request->getPathInfo());
        $response = $this->stack->execute($request, null);
        $this->log('stack start end ');
        return $response;
    }

}