<?php
namespace tests\Psr7;

use Tuum\Locator\Container;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\RequestFactory;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Web;

class WebTest extends \PHPUnit_Framework_TestCase
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
    function set_and_get()
    {
        $app = $this->app;
        $app->set('test', 'tested');
        $this->assertEquals('tested', $app->get('test'));
    }

    /**
     * @test
     */
    function no_middleware_returns_error_response()
    {
        $app = $this->app;
        $request  = RequestFactory::fromPath('test');
        $res = $app->__invoke($request);
        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($res));
        $this->assertTrue($res->isType(Response::TYPE_ERROR));
    }

    /**
     * @test
     */
    function pushed_executes_the_first_closure()
    {
        $app = $this->app;
        $app->push(function($req) { return $req.'-pushed';});
        $app->push(function($req) { return $req.'-not-reaching';});
        $res = $app('testing');
        $this->assertEquals('testing-pushed', $res);
    }

    /**
     * @test
     */
    function prepend_executes_the_last_closure()
    {
        $app = $this->app;
        $app->push(function($req) { return $req.'-pushed';});
        $app->prepend(function($req) { return $req.'-prepended';});
        $res = $app('testing');
        $this->assertEquals('testing-prepended', $res);
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
        $request  = RequestFactory::fromPath('test');
        $response = $this->app->__invoke($request);
        $this->assertEquals('2', (string) $response->getBody());
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
        $request  = RequestFactory::fromPath('test');
        $response = $this->app->__invoke($request);
        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($response));
        $this->assertEquals('/tested-location.php', $response->getHeader('location'));
        $this->assertTrue( $response->isType(Response::TYPE_REDIRECT));
        $data = $response->getData();
        $this->assertEquals('tested', $data['test']);
        $this->assertEquals(['message'=>'message-test'], $data['messages']);
        $this->assertEquals(['more'=>'done'], $data['inputs']);
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
        $request  = RequestFactory::fromPath('test');
        $response = $this->app->__invoke($request);
        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($response));
        $this->assertTrue( $response->isType(Response::TYPE_VIEW));
        $this->assertEquals('tested-view', $response->getViewFile());
        $data = $response->getData();
        $this->assertEquals('tested', $data['test']);
        $this->assertEquals(['message'=>'tested', 'error'=>true], $data['messages']);
        $this->assertEquals(['more'=>'done'], $data['inputs']);
        $this->assertEquals('tested', $data['test']);
    }
}
