<?php
namespace Tuum\Web;

use Aura\Session\SessionFactory;
use League\Container\Container;
use Tuum\Locator\Locator;
use Tuum\Router\ReverseRoute;
use Tuum\Router\Router;
use Tuum\View\ErrorView;
use Tuum\View\Renderer;
use Tuum\View\ViewEngineInterface;
use Tuum\Web\Filter\CsRfFilter;
use Tuum\Web\Stack\CsRfStack;
use Tuum\Web\Stack\Dispatcher;
use Tuum\Web\Stack\ErrorStack;
use Tuum\Web\Stack\RouterStack;
use Tuum\Web\Stack\SessionStack;
use Tuum\Web\Stack\UrlMapper;
use Tuum\Web\Stack\ViewStack;
use Tuum\Web\View\Value;
use Tuum\Web\View\View;

/**
 * Class App
 *
 * @package Tuum\Web
 *          web application.
 *
 *
 *
 */
class Web extends Application
{
    /*
     * directories
     */
    const CONFIG_DIR = 'dir.config';
    const TEMPLATE_DIR = 'dir.view';
    const DOCUMENT_DIR = 'dir.resource';
    const VAR_DATA_DIR = 'dir.variable';

    /*
     * values
     */
    const DEBUG = 'debug';
    const TOKEN_NAME = '_token';

    /*
     * services and filters
     */
    const LOGGER = 'logger';
    const ROUTE_NAMES = 'namedRoutes';
    const RENDER_ENGINE = 'renderer';
    const CS_RF_FILTER = 'csrf';
    const ROUTER_STACK = 'router-stack';

    public $debug = true;
    public $config_dir;
    public $view_dir;
    public $docs_dir;
    public $vars_dir;
    public $env_file;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param string $app_dir
     * @param bool   $debug
     * @return $this
     */
    public static function forge($app_dir, $debug=false)
    {
        $app = new self();
        $app->container  = new Container();

        // set up directories.
        $app_dir         = rtrim($app_dir, '/');
        $app->config_dir = $app_dir.'/config';
        $app->view_dir   = $app_dir.'/views';
        $app->vars_dir   = dirname($app_dir).'/var';
        $app->env_file   = $app->vars_dir.'/environment';
        $app->debug      = $debug;
        return $app;
    }

    /**
     * @return $this
     */
    public function setup()
    {
        // configuration.
        $this->configure($this->config_dir.'/configure');
        if($this->debug) {
            $this->configure($this->config_dir.'/configure-debug');
        }
        // environment.
        /** @noinspection PhpIncludeInspection */
        if(file_exists($this->env_file)) {
            $environment = (array) $this->configure($this->env_file);
            foreach($environment as $env) {
                $this->configure($this->config_dir . "/{$env}/configure");
            }
        }
        return $this;
    }

    /**
     * @return ViewEngineInterface|View
     */
    public function getViewEngine()
    {
        if($this->container->isSingleton(self::RENDER_ENGINE)) {
            return $this->container->get(self::RENDER_ENGINE);
        }
        $locator = new Locator($this->view_dir);
        if($doc_root = $this->docs_dir) {
            // also render php documents
            $locator->addRoot($doc_root);
        }
        $renderer = new Renderer($locator);
        $view = new View($renderer, new Value());
        $this->container->singleton(self::RENDER_ENGINE, $view);
        return $view;
    }

    /**
     * @param array $error_files
     * @return ErrorView
     */
    protected function getErrorView(array $error_files)
    {
        $view = new ErrorView($this->getViewEngine(), $this->debug);
        $view->setLogger($this->getLog());
        $view->error_files = $error_files;

        return $view;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLog()
    {
        return $this->container->get(self::LOGGER);
    }

    /**
     * @return CsRfFilter
     */
    protected function getCsRfFilter()
    {
        return new CsRfFilter();
    }

    /**
     * @param string $root
     * @return $this
     */
    public function pushCsRfStack($root = 'post:/*')
    {
        $this->set(self::CS_RF_FILTER, 'Tuum\Web\Filter\CsRfFilter');
        $stack = new CsRfStack($this->get(self::CS_RF_FILTER));
        $root  = (array) $root;
        foreach($root as $r) {
            $stack->setRoot($r);
        }
        $this->push($stack);
        return $this;
    }

    /**
     * @param array $error_files
     * @return $this
     */
    public function pushErrorStack(array $error_files)
    {
        $engine = $this->getErrorView($error_files);
        $stack  = new ErrorStack($engine, $this->debug);
        $stack->setLogger($this->getLog());
        $this->push($stack);
        return $this;
    }

    /**
     * @return $this
     */
    public function pushSessionStack()
    {
        $factory = new SessionFactory;
        $stack = new SessionStack($factory);
        $this->push($stack);
        return $this;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function pushUrlMapper($dir)
    {
        $view = $this->getViewEngine();
        $view->setRoot($dir); // to render some files as template.

        $locator = new Locator($dir);
        $stack = new UrlMapper($locator);
        $this->push($stack);
        return $this;
    }

    /**
     * @return $this
     */
    public function pushViewStack()
    {
        $stack = new ViewStack(
            $this->getViewEngine()
        );
        $this->push($stack);
        return $this;
    }

    /**
     * @param array $routes
     * @return $this
     */
    public function pushRoutes(array $routes)
    {
        $names = new ReverseRoute();
        foreach($routes as $route) {
            $router = new Router();
            $router->setReverseRoute($names);
            $stack = new RouterStack($router, new Dispatcher($this));
            $this->push($this->configure($route, ['stack' => $stack]));
        }
        return $this;
    }

}