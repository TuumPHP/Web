<?php
namespace Tuum\Web;

use League\Container\Container;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Tuum\Web\Psr7\Redirect;
use Tuum\Web\Psr7\Respond;
use Tuum\Web\Stack\StackProviders;
use Tuum\Web\View\ErrorView;
use Tuum\Web\View\ViewEngineInterface;
use Tuum\Web\Stack\RouterStack;
use Tuum\Web\View\ViewProviders;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class Web
 * 
 * a web application builder. 
 *
 * @package Tuum\Web
 *
 *
 */
class Web
{
    /*
     * values
     */
    const TOKEN_NAME = '_token';

    /*
     * services and filters
     */
    const ERROR_VIEWS = 'error-view-files';
    const REFERRER_URI = 'referrer-uri';

    /**
     * @var Application
     */
    private $app;

    /**
     * @var StackProviders
     */
    private $stacks;

    /**
     * @var ViewProviders
     */
    private $views;
    
    public $debug = false;

    public $app_name;
    public $app_dir;
    public $config_dir;
    public $view_dir;
    public $vars_dir;
    public $env_file;

    /**
     * constructor.
     *
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->stacks = new StackProviders($this);
        $this->views  = new ViewProviders($this);
    }

    /**
     * returns Application, $app for execution.
     * 
     * @return Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param array $config
     * @return $this
     */
    public static function forge(array $config)
    {
        $app            = new self(new Application(new Container()));
        $app->setAppRoot($config);

        return $app;
    }

    /**
     * set up working directories.
     *
     * @param array $config
     * @return $this
     */
    public function setAppRoot(array $config)
    {
        $this->app_name   = $config['app_name'];
        $this->app_dir    = rtrim($config['app_dir'], '/');
        $this->debug      = $config['debug'];
        $this->config_dir = $config['config_dir'];
        $this->view_dir   = $config['view_dir'];
        $this->vars_dir   = $config['vars_dir'];
        return $this;
    }

    /**
     * caches entire Application, $app, to a file. 
     * specify $closure to construct the application in case cache file is absent. 
     * 
     * @param \Closure $closure
     * @return $this
     */
    public function cacheApp($closure)
    {
        $cached = $this->vars_dir . '/app.cached';
        if (!$this->debug && file_exists($cached)) {
            $this->app = unserialize(\file_get_contents($cached));
            return $this;
        }
        $closure($this);
        if (!$this->debug && !file_exists($cached)) {
            \file_put_contents($cached, serialize($this->app));
            chmod($cached, 0666);
        }
        
        return $this;
    }

    /**
     * loads the main configuration for the application.
     *
     * @param null|bool $debug
     * @return $this
     */
    public function loadConfig($debug=null)
    {
        $debug = is_null($debug)? $this->debug: $debug;
        $this->configure($this->config_dir . '/configure');
        if ($debug) {
            $this->configure($this->config_dir . '/configure-debug');
        }
        return $this;
    }

    /**
     * loads the environment based configuration.
     *
     * @param string $env_file
     * @return $this
     */
    public function loadEnvironment($env_file) 
    {
        $environments = (array)$this->configure($env_file);
        foreach ($environments as $env) {
            $this->configure($this->config_dir . "/{$env}/configure");
        }
        return $this;
    }

    /**
     * constructs and pre-loads objects to be cached with $app.
     * 
     * @return $this
     */
    public function loadContainer()
    {
        // pre-load Respond for Request->respond().
        $respond  = new Respond();
        if ($this->app->exists(ViewEngineInterface::class)) {
            $respond->setViewEngine($this->app->get(ViewEngineInterface::class));
        }
        if ($this->app->exists(ErrorView::class)) {
            $respond->setErrorViews($this->app->get(ErrorView::class));
        }
        $this->app->set(Respond::class, $respond);
        
        // pre-load Redirect for Request->redirect().
        $this->app->set(Redirect::class, new Redirect());

        // pre-load RouterStack. 
        $this->getRouterStack();
        return $this;
    }

    /**
     * set up global exception handler.
     *
     * @param array $error_files
     * @return $this
     */
    public function catchError(array $error_files)
    {
        $this->app->set(Web::ERROR_VIEWS, $error_files);
        $whoops = new Run;
        if ($this->debug) {
            error_reporting(E_ALL);
            $whoops->pushHandler(new PrettyPageHandler);
        } else {
            error_reporting(E_ERROR);
            $whoops->pushHandler($this->getErrorView());
        }
        $whoops->register();
        return $this;
    }

    /**
     * @param string $key
     * @param array  $data
     * @return mixed
     */
    public function get($key, $data=[])
    {
        return $this->app->get($key, $data);
    }

    /**
     * get shared view engine, Renderer as default. 
     * 
     * @return ViewEngineInterface
     */
    public function getViewEngine()
    {
        return $this->views->getViewEngine();
    }

    /**
     * get error view render, ErrorView,
     * 
     * @return ErrorView|null
     */
    public function getErrorView()
    {
        return $this->views->getErrorView();
    }

    /**
     * get shared logger. use Monolog as default.
     * 
     * @return LoggerInterface
     */
    public function getLog()
    {
        if($this->app->exists(LoggerInterface::class)) {
            return $this->app->get(LoggerInterface::class);
        }
        $var_dir = $this->vars_dir . '/log/app.log';
        $logger  = new Logger('log');
        $logger->pushHandler(
            new FingersCrossedHandler(new StreamHandler($var_dir, Logger::DEBUG))
        );
        $this->app->set(LoggerInterface::class, $logger, true);
        return $logger;
    }

    /**
     * @param string $root
     * @return $this
     */
    public function pushCsRfStack($root = 'post:/*')
    {
        $this->push($this->stacks->getCsRfStack($root));

        return $this;
    }

    /**
     * @return $this
     */
    public function pushSessionStack()
    {
        $this->push($this->stacks->getSessionStack());

        return $this;
    }

    /**
     * @param null|ReleaseInterface $release
     * @return $this
     */
    public function pushViewStack($release=null)
    {
        $this->push($this->stacks->getViewStack($release));

        return $this;
    }

    /**
     * @param string $docs_dir
     * @param array  $options
     * @return $this
     */
    public function pushDocViewStack($docs_dir, array $options = [])
    {
        $this->push($this->stacks->getDocViewStack($docs_dir, $options));

        return $this;
    }

    /**
     * @param string $config
     * @param array  $data
     * @return $this
     */
    public function pushConfig($config, $data=[])
    {
        if($stack = $this->configure($config, $data)) {
            $this->push($stack);
        }
        return $this;
    }
    
    /**
     * @return RouterStack
     */
    public function getRouterStack()
    {
        return $this->stacks->getRouterStack();
    }
    
    /**
     * @param string $name
     * @param array  $data
     * @return mixed|null
     */
    public function configure($name, $data = [])
    {
        $data['web'] = $this;
        return $this->app->configure($name, $data);
    }

    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param ApplicationInterface $handler
     * @return $this
     */
    public function push($handler)
    {
        $this->app->push($handler);
        return $this;
    }

    /**
     * prepends a new middleware/application at the
     * beginning of the stack. returns the prepended stack.
     *
     * @param ApplicationInterface $handler
     * @return $this
     */
    public function prepend($handler)
    {
        $this->app->prepend($handler);
        return $this;
    }
}