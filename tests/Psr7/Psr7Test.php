<?php
namespace tests\Psr7;

use Tuum\Locator\Container;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\RequestFactory;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Web;

class StackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Web
     */
    var $app;

    /**
     * @var Container
     */
    var $container;

    function setup()
    {
        $this->container = Container::forge();
        $this->container->config(__DIR__ . '/config');
        $this->app = new Web($this->container);
    }

    function test0()
    {
        $this->assertEquals('Tuum\Web\Web', get_class($this->app));
    }

    /**
     * @test
     */
    function get_content_from_return_one()
    {
        $app   = $this->app;
        $app
            ->push($this->container->evaluate('return-one'))
        ;
        $request  = RequestFactory::fromPath('test');
        $response = $this->app->__invoke($request);
        $this->assertEquals(Request::class, get_class($request));
        $this->assertEquals(Response::class, get_class($response));
        $this->assertEquals('1', (string) $response->getBody());
    }
}
