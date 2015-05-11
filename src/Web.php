<?php
namespace Tuum\Web;

use League\Container\Container;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Tuum\Router\ReverseRoute;
use Tuum\Router\ReverseRouteInterface;
use Tuum\Router\Router;
use Tuum\Router\RouterInterface;
use Tuum\Web\Psr7\Redirect;
use Tuum\Web\Psr7\Respond;
use Tuum\Web\View\ErrorView;
use Tuum\Web\View\ViewEngineInterface;
use Tuum\Web\Filter\CsRfFilter;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Stack\CsRfStack;
use Tuum\Web\Stack\Dispatcher;
use Tuum\Web\Stack\DocView;
use Tuum\Web\Stack\RouterStack;
use Tuum\Web\Stack\SessionStack;
use Tuum\Web\Stack\ViewStack;
use Tuum\Web\View\View;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class App
 *
 * @package Tuum\Web
 *          web application.
 *
 *
 *
 */
class Web implements MiddlewareInterface
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
     * @return ViewEngineInterface|View
     */
    public function getViewEngine()
    {
        if($this->app->exists(ViewEngineInterface::class)) {
            return $this->app->get(ViewEngineInterface::class);
        }
        $view = View::forge($this->view_dir);
        $this->app->set(ViewEngineInterface::class, $view, true);
        return $view;
    }

    /**
     * get error view render, ErrorView,
     * 
     * @return ErrorView|null
     */
    public function getErrorView()
    {
        if($this->app->exists(ErrorView::class)) {
            return $this->app->get(ErrorView::class);
        }
        $error_files = (array)$this->app->get(Web::ERROR_VIEWS);
        if (empty($error_files)) {
            $this->app->set(ErrorView::class, null, true);
            return null;
        }
        $view = new ErrorView($this->getViewEngine(), $this->debug);
        if (isset($error_files[0])) {
            $view->default_error_file = $error_files[0];
            unset($error_files[0]);
        }
        $view->setLogger($this->getLog());
        $view->error_files = $error_files;
        $this->app->set(ErrorView::class, $view, true);

        return $view;
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
        $stack = new CsRfStack($this->app->get(CsRfFilter::class));
        $root  = (array)$root;
        foreach ($root as $r) {
            $stack->setRoot($r);
        }
        $this->push($stack);

        return $this;
    }

    /**
     * @return $this
     */
    public function pushSessionStack()
    {
        $this->push(SessionStack::forge());

        return $this;
    }

    /**
     * @param null|ReleaseInterface $release
     * @return $this
     */
    public function pushViewStack($release=null)
    {
        $stack = new ViewStack(
            $this->getViewEngine(),
            $this->getErrorView(),
            $this->getLog()
        );
        $releases = func_get_args();
        foreach($releases as $release) {
            $stack->setAfterRelease($release);
        }
        $this->push($stack);

        return $this;
    }

    /**
     * @param $docs_dir
     * @return DocView
     */
    public function getDocViewStack($docs_dir)
    {
        if (!$this->app->exists(DocView::class)) {
            $docs = DocView::forge($docs_dir, $this->vars_dir);
            $this->app->set(DocView::class, $docs);
        } else {
            $docs = $this->app->get(DocView::class);
        }
        return $docs;
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
     * @return RouterInterface
     */
    protected function getRouter()
    {
        if ($this->app->exists(RouterInterface::class)) {
            return $this->app->get(RouterInterface::class);
        }
        // use Tuum's Router class. 
        if ($this->app->exists(Router::class)) {
            // already set. clone it!
            return clone($this->app->get(Router::class));
        }
        $router = new Router();
        $router->setReverseRoute($this->getRouteNames());
        $this->app->set(Router::class, $router, true);
        return $router;
    }

    /**
     * @return RouterStack
     */
    public function getRouterStack()
    {
        if (!$this->app->exists(RouterStack::class)) {
            $router = new RouterStack($this->getRouter(), new Dispatcher($this->app));
            $this->app->set(RouterStack::class, $router);
        } else {
            $router = $this->app->get(RouterStack::class);
        }
        return $router->forge();
    }
    
    /**
     * @return ReverseRouteInterface
     */
    protected function getRouteNames()
    {
        if ($this->app->exists(ReverseRouteInterface::class)) {
            return $this->app->get(ReverseRouteInterface::class);
        }
        $names = new ReverseRoute();
        $this->app->set(ReverseRouteInterface::class, $names, true);
        return $names;
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
     * @param Request       $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        return $this->app->__invoke($request);
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