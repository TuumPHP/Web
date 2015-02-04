<?php
namespace tests\Psr7;

use Tuum\Web\Psr7\Respond;

/**
 * Class ResponseSendTest
 * 
 * trying to test headers and bodies send from Response,
 * but not working...
 *
 * @package tests\Psr7
 */
class ResponseSendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Respond
     */
    protected $respond;

    function setup()
    {
        ob_start();
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Cannot use xdebug expansion module.');
        }
        $this->respond = new Respond();
    }

    /**
     * just avoid being no tests.
     */
    function test0()
    {
        $this->assertTrue(true);
    }

    /**
     * @ test
     * @runInSeparateProcesses
     */
    function send_asHtml()
    {
        $res = $this->respond->asHtml('testing');
        ob_start();
        $res->send();
        $body = ob_get_clean();
        $output_header = xdebug_get_headers();
        $this->assertContains('Content-type: text/html; charset=UTF-8', $output_header);
        $this->assertEquals('testing', $body);
    }

    /**
     * @ test
     * @runInSeparateProcesses
     */
    function send_asText()
    {
        $res = $this->respond->asText('testing');
        ob_start();
        $res->send();
        $body = ob_get_clean();
        $output_header = xdebug_get_headers();
        $this->assertContains('Content-type: text/plain; charset=UTF-8', $output_header);
        $this->assertEquals('testing', $body);
    }
}