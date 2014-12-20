<?php
namespace tests\App\Stackable;

use Tuum\Locator\Container;
use Tuum\Web\App;
use Tuum\Web\Http\Redirect;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\View;

require_once(__DIR__ . '/../autoloader.php');

class StackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var App
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
        $this->app = App::forge($this->container);
    }

    function test0()
    {
        $this->assertEquals('Tuum\Web\App', get_class($this->app));
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
        $request  = Request::startPath('test');
        $response = $this->app->handle($request);
        $this->assertEquals('Tuum\Web\Http\Request', get_class($request));
        $this->assertEquals('Tuum\Web\Http\Response', get_class($response));
        $this->assertEquals('1', $response->getContent());
    }

    /**
     * @test
     */
    function get_content_from_return_one_and_increment()
    {
        $app   = $this->app;
        $app
            ->push($this->container->evaluate('increment'))
            ->push($this->container->evaluate('return-one'))
        ;
        $request  = Request::startPath('test');
        $response = $this->app->handle($request);
        $this->assertEquals('2', $response->getContent());
    }

    /**
     * @test
     */
    function location()
    {
        $app   = $this->app;
        $app
            ->push($this->container->evaluate('location'))
        ;
        $request  = Request::startPath('test');
        /** @var Redirect $response */
        $response = $this->app->handle($request);
        $this->assertEquals('Tuum\Web\Http\Redirect', get_class($response));
        $this->assertEquals('tested-location.php', $response->getTargetUrl());
        $data = $response->getData();
        $this->assertEquals('tested', $data['test']);
        $this->assertEquals(['message'=>'message-test'], $data['messages']);
        $this->assertEquals(['more'=>'done'], $data['input']);
    }

    /**
     * @test
     */
    function view()
    {
        $app   = $this->app;
        $app
            ->push($this->container->evaluate('view'))
        ;
        $request  = Request::startPath('test');
        /** @var View $response */
        $response = $this->app->handle($request);
        $this->assertEquals('Tuum\Web\Http\View', get_class($response));
        $this->assertEquals('tested-view', $response->getFile());
        $data = $response->getData();
        $this->assertEquals('tested', $data['test']);
        $this->assertEquals(['error'=>'tested'], $data['messages']);
        $this->assertEquals('tested', $data['test']);
    }

}
