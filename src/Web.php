<?php
namespace Tuum\Web;

use League\Container\Container;
use Tuum\Locator\Locator;
use Tuum\Web\Middleware\BeforeFilterTrait;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

/**
 * Class Web
 *
 * main web application class. acts a top middleware.
 *
 * @package Tuum\Web
 */
class Web implements MiddlewareInterface
{
    use MiddlewareTrait;

    use BeforeFilterTrait;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Locator   $locator
     * @param Container $container
     */
    public function __construct($locator, $container)
    {
        $this->locator   = $locator;
        $this->container = $container;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->container->add($key, $value);
    }

    /**
     * @param string $key
     * @param array  $data
     * @return mixed
     */
    public function get($key, $data=[])
    {
        $data['app'] = $this;
        return $this->container->get($key, $data);
    }

    /**
     * add a config directory for the container.
     *
     * @param string $root
     */
    public function setConfigRoot($root)
    {
        $this->locator->addRoot($root);
    }

    /**
     * @param string $__config
     * @return mixed|null
     */
    public function configure($__config, $__data=[])
    {
        if($__file = $this->locator->locate($__config.'.php')) {
            if(file_exists($__file)) {
                $app = $this;
                $dic = $this->container;
                extract($__data);
                /** @noinspection PhpIncludeInspection */
                return include($__file);
            }
        }
        return null;
    }

    /**
     * @param string $root
     * @return $this
     */
    public function setRenderRoot($root)
    {
        $engine = $this->get(App::RENDER_ENGINE);
        $engine->locator->addRoot($root);
        return $this;
    }

    /**
     * @param string $routes
     * @return mixed
     */
    public function execute($routes)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $app = $this; // to be used in the included file.
        return include($routes);
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function __invoke($request)
    {
        if(is_object($request) && method_exists($request, 'setWebApp')) {
            $request->setWebApp($this);
        }
        $app = $this->next;
        if($app) {
            return $app($request);
        }
        return $request->respond()->asError();
    }

    /**
     * start a new web application.
     * have same container but no middleware.
     *
     * @return Web
     */
    public function cloneApp()
    {
        $new = clone($this);
        $new->next = null;
        return $new;
    }
}