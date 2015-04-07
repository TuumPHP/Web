<?php
namespace tests\Controller;

use tests\Controller\ctrl\ByMethodController;
use tests\Controller\ctrl\ResourceController;
use tests\Controller\ctrl\TestController;
use Tuum\Web\Psr7\RequestFactory;
use Tuum\Web\View\Value;

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
        $view = (new Value)->forge($data);
        $this->assertEquals(
            '<div class="alert alert-success">dispatched</div>'.
            '<div class="alert alert-info">noticed</div>'.
            '<div class="alert alert-danger">withoutError</div>',
            (string)$view->message);
        $this->assertEquals('tested', $view->inputs->raw('input'));
        $this->assertEquals('error', $view->errors->raw('has'));
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
    function ResourceDispatch_returns_options_in_allow_header()
    {
        $controller = new ResourceController();
        $request    = RequestFactory::fromPath('/', 'options');
        $response   = $controller->__invoke($request);
        $this->assertEquals('GET,HEAD,OPTIONS,POST', $response->getHeader('Allow'));

        $request    = RequestFactory::fromPath('/123', 'options');
        $response   = $controller->__invoke($request);
        $this->assertEquals('GET,HEAD,OPTIONS,POST', $response->getHeader('Allow'));
    }

    /**
     * @test
     */
    function DispatchByMethod_returns_options_in_allow_header()
    {
        $controller = new ByMethodController();
        $request    = RequestFactory::fromPath('/', 'options');
        $response   = $controller->__invoke($request);
        $this->assertEquals('GET,HEAD,OPTIONS,PUT', $response->getHeader('Allow'));

        $request    = RequestFactory::fromPath('/123', 'options');
        $response   = $controller->__invoke($request);
        $this->assertEquals('GET,HEAD,OPTIONS,PUT', $response->getHeader('Allow'));
    }

    /**
     * @test
     */
    function ResourceDispatch_returns_response_without_body()
    {
        $controller = new ResourceController();
        $resGet     = $controller->__invoke(RequestFactory::fromPath('/123', 'get'));
        $resHead    = $controller->__invoke(RequestFactory::fromPath('/123', 'head'));
        $bodyGet    = (string) $resGet->getBody();
        $bodyHead   = (string) $resHead->getBody();
        $this->assertEquals($resGet->getHeaders(), $resHead->getHeaders());
        $this->assertNotEmpty($bodyGet);
        $this->assertEmpty($bodyHead);

        // returns null if bad URL is given.
        $resHead    = $controller->__invoke(RequestFactory::fromPath('/123/bad', 'head'));
        $this->assertNull($resHead);
    }

    /**
     * @test
     */
    function DispatchByMethod_returns_response_without_body()
    {
        $controller = new ByMethodController();
        $resGet     = $controller->__invoke(RequestFactory::fromPath('/', 'get'));
        $resHead    = $controller->__invoke(RequestFactory::fromPath('/', 'head'));
        $bodyGet    = (string) $resGet->getBody();
        $bodyHead   = (string) $resHead->getBody();
        $this->assertEquals($resGet->getHeaders(), $resHead->getHeaders());
        $this->assertNotEmpty($bodyGet);
        $this->assertEmpty($bodyHead);
    }
}
