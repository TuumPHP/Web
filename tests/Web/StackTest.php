<?php
namespace tests\App\Stackable;

use Tuum\Locator\Container;
use Tuum\Web\App;

require_once( __DIR__.'/../autoloader.php' );

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
        $this->container->config(__DIR__.'/config');
        $this->app = App::forge($this->container);
    }
    
    function test0()
    {
        $this->assertEquals( 'Tuum\Web\App', get_class($this->app) );
    }

    /**
     * @test
     */
    function middleware_to_increment_contents()
    {
        $app = $this->app;
        $stack = $this->container->evaluate('return-one');
        $app->push( $stack );
    }

}
