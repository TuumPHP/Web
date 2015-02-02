<?php
namespace Tuum\Web;

use Tuum\Locator\Container;
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
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->container->set($key, $value);
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
}