<?php
namespace Tuum\Web\Stack;

use Tuum\Router\ReverseRoute;
use Tuum\Router\ReverseRouteInterface;
use Tuum\Router\Router;
use Tuum\Router\RouterInterface;
use Tuum\Web\Application;
use Tuum\Web\Filter\CsRfFilter;
use Tuum\Web\ReleaseInterface;
use Tuum\Web\Web;

class StackProviders
{
    /**
     * @var Web
     */
    private $web;

    /**
     * @var Application
     */
    private $app;

    /**
     * @param Web $web
     */
    public function __construct($web)
    {
        $this->web = $web;
        $this->app = $web->getApp();
    }

    /**
     * @param string $root
     * @return CsRfStack
     */
    public function getCsRfStack($root)
    {
        $stack = new CsRfStack($this->web->get(CsRfFilter::class));
        $root  = (array)$root;
        foreach ($root as $r) {
            $stack->setRoot($r);
        }
        return $stack;
    }

    /**
     * @return SessionStack
     */
    public function getSessionStack()
    {
        return SessionStack::forge();
    }

    /**
     * @param null|ReleaseInterface $release
     * @return ViewStack
     */
    public function getViewStack($release=null)
    {
        $stack = new ViewStack(
            $this->web->getViewEngine(),
            $this->web->getErrorView(),
            $this->web->getLog()
        );
        $releases = func_get_args();
        foreach($releases as $release) {
            $stack->setAfterRelease($release);
        }

        return $stack;
    }

    /**
     * @param string $docs_dir
     * @param array  $options
     * @return DocView
     */
    public function getDocViewStack($docs_dir, array $options = [])
    {
        if (!$this->app->exists(DocView::class)) {
            $docs = DocView::forge($docs_dir, $this->web->vars_dir);
            $docs->options($options);
            $this->app->set(DocView::class, $docs);
        } else {
            $docs = $this->app->get(DocView::class);
        }
        return $docs;
    }

    /**
     * @return RouterStack
     */
    public function getRouterStack()
    {
        if (!$this->app->exists(RouterStack::class)) {
            $router = new RouterStack($this->getRouter(), new Dispatcher($this->app));
            $this->app->set(RouterStack::class, $router, true);
        } else {
            $router = $this->app->get(RouterStack::class);
        }
        return $router->forge();
    }
    
    /**
     * @return RouterInterface
     */
    public function getRouter()
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
}