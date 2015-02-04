<?php
namespace tests\Psr7;

use Tuum\Web\Psr7\Respond;
use Tuum\Web\Psr7\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Respond
     */
    protected $respond;

    function setup()
    {
        $this->respond = new Respond();
    }
    
    function test0()
    {
        $response = $this->respond->asHtml('test');
        $this->assertEquals('Tuum\Web\Psr7\Respond',  get_class($this->respond));
        $this->assertEquals('Tuum\Web\Psr7\Response', get_class($response));
    }

    /**
     * @test
     */
    function with_data()
    {
        $res1 = $this->respond->asHtml('test');
        $this->assertEquals( null, $res1->getLocation() );

        $res2 = $res1->withData( 'test', 'tested');
        $this->assertNotSame($res1, $res2);
        
        $data = $res2->getData('test');
        $this->assertEquals('tested', $data);

        $data = $res2->getData();
        $this->assertEquals(['test' => 'tested'], $data);

        $res2 = $res2->withData( ['more' => 'done' ]);
        $data = $res2->getData();
        $this->assertEquals([
            'test' => 'tested',
            'more' => 'done',
        ], $data);
    }

    /**
     * @test
     */
    function location_redirect()
    {
        $res = $this->respond->asRedirectUri('/test');
        $this->assertEquals( '/test', $res->getLocation() );
        $this->assertTrue($res->isType(Response::TYPE_REDIRECT));
    }

    /**
     * @test
     */
    function error_response()
    {
        $res = $this->respond->asError();
        $this->assertEquals( null, $res->getLocation() );
        $this->assertTrue($res->isType(Response::TYPE_ERROR));
        $this->assertEquals('500', $res->getStatusCode());

        $res = $this->respond->asNotFound();
        $this->assertTrue($res->isType(Response::TYPE_ERROR));
        $this->assertEquals('404', $res->getStatusCode());
    }

    /**
     * @test
     */
    function asJson()
    {
        $res = $this->respond->asJson(['test' => 'tested']);
        $obj = json_decode((string) $res->getBody());
        $std = new \stdClass;
        $std->test = 'tested';
        $this->assertEquals($std, $obj);
    }
}
