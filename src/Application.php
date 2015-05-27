<?php
namespace Tuum\Web;

use League\Container\Container;
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
class Application implements MiddlewareInterface
{
    use MiddlewareTrait;

    use BeforeFilterTrait;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param bool   $singleton
     */
    public function set($key, $value, $singleton = false)
    {
        $this->container->add($key, $value, $singleton);
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
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->container[$key]);
    }

    /**
     * @param string $__config
     * @param array  $__data
     * @return mixed|null
     */
    public function configure($__config, $__data = [])
    {
        $__file = $__config . '.php';
        if (!file_exists($__file)) {
            throw new \InvalidArgumentException('Cannot find configuration file: ' . $__config);
        }
        if (file_exists($__file)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $app = $this;
            /** @noinspection PhpUnusedLocalVariableInspection */
            $dic = $this->container;
            extract($__data);
            /** @noinspection PhpIncludeInspection */
            return include($__file);
        }
        return null;
    }

    /**
     * @param Request       $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        $app = $this->next;
        if ($app) {
            return $app($request);
        }
        return $request->respond()->asError();
    }

    /**
     * start a new web application.
     * have same container but no middleware.
     *
     * @return Application
     */
    public function cloneApp()
    {
        $new       = clone($this);
        $new->next = null;
        return $new;
    }
}