<?php
namespace tests\Controller;

use tests\Controller\ctrl\ByMethodController;
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
    }
}
