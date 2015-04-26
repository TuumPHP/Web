<?php
namespace tests\Viewer;

use Tuum\Web\View\ViewEngineInterface;
use Tuum\Web\View\ViewStream;

require_once(dirname(__DIR__).'/autoloader.php');

class render implements ViewEngineInterface
{
    public function render($file, $data = [])
    {
        $string = "hi\n";
        foreach($data as $key => $val) {
            $string .= "$key: $val\n";
        }
        return $string;
    }

    public function modRenderer($mod)
    {
    }
}

class Val
{
    /**
     * @var array
     */
    public $data;

    function withData(array $data)
    {
        $self = clone($this);
        $self->data = $data;
        return $self;
    }
    
    function __toString()
    {
        $string = "hi\n";
        foreach($this->data as $key => $val) {
            $string .= "$key: $val\n";
        }
        return $string;
    }
}

class ViewStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ViewStream
     */
    public $view;

    function setup()
    {
        $this->view = new ViewStream(new render(), new Val());
    }
    
    function test0()
    {
        $this->assertEquals('Tuum\Web\View\ViewStream', get_class($this->view));
    }

    /**
     * @test
     */
    function view()
    {
        $view = $this->view;
        $view->setView("test.view", ['all' => 'clear']);
        $this->assertEquals("hi\nall: clear\n", (string) $view);
        $this->assertEquals("", $view->getContents());
    }

    /**
     * @test
     */
    function getContent_renders_only_once()
    {
        $view = $this->view;
        $view->setView("test.view", ['all' => 'clear']);
        $this->assertEquals("hi\nall: clear\n", $view->getContents());
        $this->assertEquals("", $view->getContents());
    }

    /**
     * @test
     */
    function eof_returns_true_then_false_after_render_and_true_after_rewind()
    {
        $view = $this->view;
        $view->setView("test.view", ['all' => 'clear']);
        $this->assertEquals(false, $view->eof());
        $this->assertEquals("hi\nall: clear\n", $view->getContents());
        $this->assertEquals(true, $view->eof());
        $this->assertEquals("", $view->getContents());
        $view->rewind();
        $this->assertEquals(false, $view->eof());
        $this->assertEquals("hi\nall: clear\n", $view->getContents());
    }

    /**
     * @test
     */
    function all_meta_stuff()
    {
        $view = $this->view;
        $view->setView("test.view", ['all' => 'clear']);
        $this->assertEquals(null, $view->getSize());
        $this->assertEquals(true, $view->isReadable());
        $this->assertEquals(false, $view->isSeekable());
        $this->assertEquals(false, $view->isWritable());
        $this->assertEquals(false, $view->read(1));
        $this->assertEquals(false, $view->seek(1));
        $this->assertEquals(false, $view->tell());
        $this->assertEquals(false, $view->write('bad'));
    }

    /**
     * @test
     */
    function get_meta()
    {
        $view = $this->view;
        $view->setView("test.view", ['all' => 'clear']);
        $meta = $view->getMetadata();
        $this->assertTrue(is_array($meta));
        $this->assertEquals($meta['uri'], $view->getMetadata('uri'));
    }
}
