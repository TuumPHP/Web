<?php
namespace tests\Stack;

use Aura\Session\Segment;
use Aura\Session\SessionFactory;
use Tuum\Web\Stack\SessionStack;

class SessionStackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SessionStack
     */
    private $stack;

    /**
     * @var SessionFactory
     */
    private $factory;

    function setup()
    {
        $this->factory = new SessionFactory;
        $this->stack = new SessionStack($this->factory);
    }

    /**
     * @return Segment
     */
    function getSessionSegment()
    {
        return $this->factory->newInstance($_COOKIE)->getSegment('TuumPHP/WebApplication');
    }

    function test0()
    {
        $this->assertEquals('Tuum\Web\Stack\SessionStack', get_class($this->stack));
    }
}
