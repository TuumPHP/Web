<?php
namespace Tuum\Web;

use Tuum\Locator\Container;
use Tuum\Web\Middleware\BeforeFilterTrait;
use Tuum\Web\Middleware\Middleware;
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
class Web extends Middleware
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
     * @return mixed
     */
    public function get($key)
    {
        return $this->container->get($key);
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function __invoke($request)
    {
        $request->setWebApp($this);
        $app = $this->next;
        if($app) {
            return $app($request);
        }
        return $request->respond()->asError();
    }
}