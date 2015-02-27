<?php
namespace tests\Psr7;

use Tuum\Locator\Container;
use Tuum\Locator\Locator;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\RequestFactory;
use Tuum\Web\Application;

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
        $request = RequestFactory::fromPath('/path/to', 'get');
        $request->respondWith()->with(
            'test', 'tested'
        );
        $request->respondWith()->with([
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
    function filter_applies_request()
    {
        $con = new Container(new Locator(__DIR__.'/config'));
        $app = new Application($con);
        $con->set('test-filter', function($request) {
            /** @var Request $request */
            $request->respondWith()->with(
                'tested', 'filter'
            );
        });
        $request = RequestFactory::fromPath('/path/to', 'get');
        $request->setWebApp($app);
        $request->filter('test-filter');

        $response = $request->respond()->asView('test');
        $data = $response->getData();
        $this->assertEquals('filter', $data['tested']);
    }

    /**
     * @test
     */
    function respond()
    {
        $request = RequestFactory::fromPath('/path/to', 'get');
        $request->respondWith()->with( 'test', 'tested');
        $respond = $request->respond()
            ->withMessage('hello')
            ->withInput( ['more' => 'done'])
            ->withInputErrors(['input' => 'errors'])
        ;
        $this->assertEquals('Tuum\Web\Psr7\Respond', get_class($respond));
        $response= $respond->asRedirectUri('tested');
        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($response));
        $data = $response->getData();
        $this->assertEquals('tested',                $data['test']);
        $this->assertEquals([['message' => 'hello','type'=>'message']],  $data['messages']);
        $this->assertEquals(['more'    => 'done'],   $data['inputs']);
        $this->assertEquals(['input'   => 'errors'], $data['errors']);
    }
}