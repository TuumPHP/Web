<?php
namespace tests\Controller;

use tests\Controller\ctrl\ByMethodController;
use tests\Controller\ctrl\ResourceController;
use tests\Controller\ctrl\TestController;
use Tuum\Web\Psr7\RequestFactory;
use Tuum\Web\Viewer\View;

require_once(dirname(__DIR__).'/autoloader.php');

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function blank_controller_dispatch_returns_response()
    {
        $request    = RequestFactory::fromPath('test');
        $controller = new TestController();
        $response   = $controller->__invoke($request);

        $this->assertEquals('tests\Controller\ctrl\TestController', get_class($controller));
        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($response));
        $this->assertEquals('responded', $response->getViewFile());

        $data = $response->getData();
        $view = new View($data);
        $this->assertEquals(
            '<div class="alert alert-success">dispatched</div>'.
            '<div class="alert alert-info">noticed</div>'.
            '<div class="alert alert-danger">withoutError</div>',
            (string)$view->message);
        $this->assertEquals('tested', $view->inputs->get('input'));
        $this->assertEquals('error', $view->errors->get('has'));
    }

    /**
     * @test
     */
    function ByMethod_controller_returns_by_method()
    {
        $request    = RequestFactory::fromPath('test', 'put');
        $controller = new ByMethodController();
        $response   = $controller->__invoke($request);
        $this->assertEquals('on-put', (string) $response->getBody());

        $request    = RequestFactory::fromPath('test', 'post');
        $controller = new ByMethodController();
        $response   = $controller->__invoke($request);
        $this->assertNull($response);
    }

    /**
     * @test
     */
    function Resource_controller_returns_based_on_route()
    {
        $request    = RequestFactory::fromPath('/123', 'get');
        $controller = new ResourceController();
        $response   = $controller->__invoke($request);
        $this->assertEquals('on-get:123', (string) $response->getBody());

        $request    = RequestFactory::fromPath('/bad/route', 'get');
        $controller = new ResourceController();
        $response   = $controller->__invoke($request);
        $this->assertNull($response);
    }

    /**
     * @test
     */
    function http_options_returns_allow_header()
    {
        $controller = new ResourceController();
        $request    = RequestFactory::fromPath('/', 'options');
        $response   = $controller->__invoke($request);
        $this->assertEquals('GET,OPTIONS,POST', $response->getHeader('Allow'));

        $request    = RequestFactory::fromPath('/123', 'options');
        $response   = $controller->__invoke($request);
        $this->assertEquals('GET,OPTIONS,POST', $response->getHeader('Allow'));
    }
}
