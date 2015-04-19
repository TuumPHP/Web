<?php
namespace Tuum\Web;

use Aura\Session\SessionFactory;
use League\Container\Container;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Tuum\Locator\Locator;
use Tuum\Router\ReverseRoute;
use Tuum\Router\ReverseRouteInterface;
use Tuum\Router\Router;
use Tuum\Router\RouterInterface;
use Tuum\View\ErrorView;
use Tuum\View\Renderer;
use Tuum\View\ViewEngineInterface;
use Tuum\Web\Filter\CsRfFilter;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Stack\CsRfStack;
use Tuum\Web\Stack\Dispatcher;
use Tuum\Web\Stack\RouterStack;
use Tuum\Web\Stack\SessionStack;
use Tuum\Web\Stack\ViewStack;
use Tuum\Web\View\Value;
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
    public $config_dir;
    public $view_dir;
    public $docs_dir;
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
     * @param string $app_dir
     * @param bool   $debug
     * @return $this
     */
    public static function forge($app_dir, $debug=false)
    {
        $app            = new self(new Application(new Container()));
        $app->debug     = $debug;
        $app->setAppRoot($app_dir);

        return $app;
    }

    /**
     * all in one set up.
     *
     * @param string $env_file
     * @return $this
     */
    public function setup($env_file)
    {
        $this
            ->loadConfig()
            ->loadEnvironment($env_file)
            ->catchError();
        return $this;
    }

    /**
     * set up working directories.
     *
     * @param string $app_dir
     * @return $this
     */
    public function setAppRoot($app_dir)
    {
        $app_dir          = rtrim($app_dir, '/');
        $this->config_dir = $app_dir . '/config';
        $this->view_dir   = $app_dir . '/views';
        $this->vars_dir   = dirname($app_dir) . '/var';
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
     * set up global exception handler.
     *
     * @param null|bool $debug
     * @return $this
     */
    public function catchError($debug=null)
    {
        $debug = is_null($debug)? $this->debug: $debug;
        $whoops = new Run;
        if ($debug) {
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
     * get shared view engine, Renderer as default. 
     * 
     * @return ViewEngineInterface|View
     */
    public function getViewEngine()
    {
        if($this->app->exists(ViewEngineInterface::class)) {
            return $this->app->get(ViewEngineInterface::class);
        }
        $locator = new Locator($this->view_dir);
        if ($doc_root = $this->docs_dir) {
            // also render php documents
            $locator->addRoot($doc_root);
        }
        $renderer = new Renderer($locator);
        $view = new View($renderer, new Value());
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
        $error_files = (array)$this->app->get(Web::ERROR_VIEWS);
        if (empty($error_files)) {
            return null;
        }
        $view = new ErrorView($this->getViewEngine(), $this->debug);
        if (isset($error_files[0])) {
            $view->default_error_file = $error_files[0];
            unset($error_files[0]);
        }
        $view->setLogger($this->getLog());
        $view->error_files = $error_files;

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
        $factory = new SessionFactory;
        $stack   = new SessionStack($factory);
        $this->push($stack);

        return $this;
    }

    /**
     * @param string $docs_dir
     * @return $this
     */
    public function pushDocView($docs_dir)
    {
        $view = $this->getViewEngine();
        $view->setRoot($docs_dir); // to render some files as template.

        $docs = new \Tuum\Web\Stack\DocView(
            new \Tuum\Locator\Locator($docs_dir),
            \Tuum\Locator\CommonMark::forge(
                $docs_dir,
                $this->vars_dir . '/markUp')
        );
        $docs->enable_raw = true;
        $this->push($docs);

        return $this;
    }

    /**
     * @return $this
     */
    public function pushViewStack()
    {
        $stack = new ViewStack(
            $this->getViewEngine(),
            $this->getErrorView(),
            $this->getLog()
        );
        $this->push($stack);

        return $this;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        if ($this->app->exists(RouterInterface::class)) {
            return $this->app->get(RouterInterface::class);
        }
        $this->app->set(RouterInterface::class, function() {
            $router = new Router();
            $router->setReverseRoute($this->getRouteNames());
            return $router;
        });
        return $this->app->get(RouterInterface::class);
    }

    /**
     * @return ReverseRouteInterface
     */
    public function getRouteNames()
    {
        if ($this->app->exists(ReverseRouteInterface::class)) {
            return $this->app->get(ReverseRouteInterface::class);
        }
        $names = new ReverseRoute();
        $this->app->set(ReverseRouteInterface::class, $names, true);
        return $names;
    }
    
    /**
     * @param array $routes
     * @return $this
     */
    public function pushRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $router = $this->getRouter();
            $stack = new RouterStack($router, new Dispatcher($this->app));
            $this->push($this->configure($route, ['stack' => $stack]));
        }

        return $this;
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
     * @param callable|null $next
     * @return null|Response
     */
    public function __invoke($request, $next = null)
    {
        return $this->app->__invoke($request, $next);
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