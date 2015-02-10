<?php
namespace tests\Viewer;

use Tuum\Web\Viewer\Data;

require_once(dirname(__DIR__).'/autoloader.php');

class DataTest extends \PHPUnit_Framework_TestCase
{
    function test0()
    {
        $this->assertEquals('Tuum\Web\Viewer\Data', get_class(new Data()));
    }

    /**
     * @test
     */
    function view_returns_data()
    {
        $data = [
            'text' => 'tested',
            'html' => '<bold>',
        ];
        $view = new Data($data);

        // getting values
        $this->assertEquals('tested', $view['text']);
        $this->assertEquals('tested', $view->text);
        $this->assertEquals('&lt;bold&gt;', $view['html']);
        $this->assertEquals('<bold>', $view->html);

        // check existence
        $this->assertTrue($view->offsetExists('text'));
        $this->assertFalse($view->offsetExists('none'));
        $this->assertTrue(isset($view['text']));
        $this->assertFalse(isset($view['none']));

        // html escaping
        $this->assertEquals('&lt;bold&gt;', $view->safe('html'));
        $this->assertEquals(null, $view->safe('none'));

        // hidden tags
        $this->assertEquals(null, $view->hiddenTag('none'));
        $this->assertEquals("<input type='hidden' name='text' value='tested' />", $view->hiddenTag('text'));
        $this->assertEquals("<input type='hidden' name='html' value='&lt;bold&gt;' />", $view->hiddenTag('html'));
    }

    /**
     * @test
     */
    function view_iteration()
    {
        $data = [
            'text' => 'tested',
            'more' => 'done',
        ];
        $view = new Data($data);
        foreach($view as $key => $value) {
            $this->assertEquals($data[$key], $value);
        }
    }
    /**
     * @test
     */
    function withKey_returns_new_view_object()
    {
        $view1 = new Data(['test'=>['more' => 'testing']]);
        $view2 = $view1->withKey('test');
        $this->assertEquals(['more' => 'testing'], $view1->test);
        $this->assertEquals('testing', $view2->more);
    }

    /**
     * @test
     */
    function safe_escapes_and_htmlSafe_as_default()
    {
        $view = new Data();
        $this->assertEquals('&lt;bold&gt;', $view->esc('<bold>'));
        $this->assertEquals('a&#039;b', $view->esc('a\'b'));

        // change escape functions
        $esc = \Tuum\Web\Viewer\View::$escape;
        \Tuum\Web\Viewer\View::$escape = 'addslashes';
        $this->assertEquals('<bold>', $view->esc('<bold>'));
        $this->assertEquals('a\\\'b', $view->esc('a\'b'));
        \Tuum\Web\Viewer\View::$escape = $esc;
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function offsetSet_throws_exception()
    {
        $view = new Data();
        $view['test'] = 'tested';
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function offsetUnSet_throws_exception()
    {
        $view = new Data(['test'=>'tested']);
        unset($view['test']);
    }

    /**
     * @test
     */
    function can_set_value_as_property()
    {
        $view = new Data();
        $view->test = 'tested';
        $this->assertEquals('tested', $view->test);
        $this->assertEquals(null, $view['test']);
    }

    /**
     * @test
     */
    function view_can_handle_object()
    {
        $obj = new \stdClass();
        $obj->test = 'tested';
        $view = new Data($obj);
        $this->assertEquals('tested', $view->get('test'));
    }

    /**
     * @test
     */
    function view_can_handle_arrayAccess_object()
    {
        $obj = new \ArrayObject(['test'=>'tested']);
        $view = new Data($obj);
        $this->assertEquals('tested', $view->get('test'));
    }
    /**
     * @test
     */
    function withKey_creates_new_view()
    {
        $obj1 = new \stdClass();
        $obj1->test = 'tested';
        $obj2 = new \stdClass();
        $obj2->test = 'done';
        $view = new Data([
            'list' => [
                $obj1,
                $obj2
            ]
        ]);
        $list = $view->withKey('list');
        $this->assertEquals('Tuum\Web\Viewer\Data', get_class($list));

        $answer = ['tested', 'done'];
        foreach($list->getKeys() as $key) {
            $object = $list->withKey($key);
            $this->assertEquals('Tuum\Web\Viewer\Data', get_class($object));
            $this->assertEquals($answer[$key], $object->test);
        }
    }


}
