<?php
namespace tests\Psr7;

use Tuum\Web\Psr7\Response;
use Tuum\Web\View\Value;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\RequestFactory;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    function test0()
    {
        $request = RequestFactory::fromPath('/path/to', 'get');
        $this->assertEquals('Tuum\Web\Psr7\Request', get_class($request));
    }

    /**
     * @test
     */
    function path_to_match_returns_uri_path()
    {
        $request = RequestFactory::fromPath('/path/to', 'get');
        $this->assertEquals('/path/to', $request->getPathToMatch());
        
        $newReq = $request->withPathToMatch('', '/another/path');
        $this->assertEquals('/path/to', $request->getPathToMatch());
        $this->assertEquals('/another/path', $newReq->getPathToMatch());
    }

    /**
     * @test
     */
    function respond_with_sets_data_in_response()
    {
        /** @var Request $request */
        $request = RequestFactory::fromPath('/path/to', 'get');
        $request = $request->withAttribute(
            'test', 'tested'
        );
        $request = $request->withAttributes([
            'more' => 'done',
        ]);
        $response = $request->respond()->asView('test');
        $data = $response->getData();
        $this->assertEquals('tested', $data['test']);
        $this->assertEquals('done', $data['more']);
    }

    /**
     * @test
     */
    function respond()
    {
        /** @var Request $request */
        $request = RequestFactory::fromPath('/path/to', 'get');
        $request = $request->withAttribute( 'test', 'tested');
        $respond = $request->respond()
            ->withMessage('hello')
            ->withInput( ['more' => 'done'])
            ->withInputErrors(['input' => 'errors'])
        ;
        $this->assertEquals('Tuum\Web\Psr7\Respond', get_class($respond));
        $response= $respond->asView('tested');
        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($response));
        $data = $response->getData();
        $this->assertEquals('tested',                $data['test']);
        $this->assertEquals([['message' => 'hello','type'=>'message']],  $data[Value::MESSAGE]);
        $this->assertEquals(['more'    => 'done'],   $data[Value::INPUTS]);
        $this->assertEquals(['input'   => 'errors'], $data[Value::ERRORS]);
    }

    /**
     * @test
     */
    function redirect_with_base_path()
    {
        /** @var Request $request */
        $request = RequestFactory::fromPath('/path/to', 'get');
        $request = $request->withAttribute( 'test', 'tested')->withAttribute('more', 'none');
        $request = $request->withPathToMatch('/path', 'to');
        $redirect= $request->redirect(['test'])
            ->withMessage('hello')
            ->withInput( ['more' => 'done'])
            ->withInputErrors(['input' => 'errors'])
        ;
        $this->assertEquals('Tuum\Web\Psr7\Redirect', get_class($redirect));
        $response= $redirect->toBasePath('more');

        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($response));
        $this->assertTrue($response->isType(Response::TYPE_REDIRECT));
        $this->assertEquals('/path/more', $response->getLocation());

        $data = $response->getData();
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('more', $data);
        $this->assertEquals('tested', $data['test']);

        $this->assertEquals([['message' => 'hello','type'=>'message']],  $data[Value::MESSAGE]);
        $this->assertEquals(['more'    => 'done'],   $data[Value::INPUTS]);
        $this->assertEquals(['input'   => 'errors'], $data[Value::ERRORS]);    }
}