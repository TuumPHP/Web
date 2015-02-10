<?php
namespace tests\Viewer;

use Tuum\Web\Viewer\View;

require_once(dirname(__DIR__).'/autoloader.php');

class ViewTest extends \PHPUnit_Framework_TestCase
{
    function test0()
    {
        $this->assertEquals('Tuum\Web\Viewer\View', get_class(new View()));
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
        $view = new View($data);

        // getting values
        $this->assertEquals('tested', $view['text']);
        $this->assertEquals('tested', $view->text);
        $this->assertEquals('<bold>', $view['html']);
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
        $view = new View($data);
        foreach($view as $key => $value) {
            $this->assertEquals($data[$key], $value);
        }
    }
    /**
     * @test
     */
    function withData_returns_new_view_object()
    {
        $view1 = new View(['test'=>'testing']);
        $view2 = $view1->withData(['test'=>'tested']);
        $this->assertEquals('testing', $view1->test);
        $this->assertEquals('tested', $view2->test);
    }

    /**
     * @test
     */
    function e_escapes_and_htmlSafe_as_default()
    {
        $view = new View();
        $this->assertEquals('&lt;bold&gt;', $view->e('<bold>'));
        $this->assertEquals('a&#039;b', $view->e('a\'b'));

        // change escape functions
        $view->escape = 'addslashes';
        $this->assertEquals('<bold>', $view->e('<bold>'));
        $this->assertEquals('a\\\'b', $view->e('a\'b'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function offsetSet_throws_exception()
    {
        $view = new View();
        $view['test'] = 'tested';
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function offsetUnSet_throws_exception()
    {
        $view = new View(['test'=>'tested']);
        unset($view['test']);
    }

    /**
     * @test
     */
    function can_set_value_as_property()
    {
        $view = new View();
        $view->test = 'tested';
        $this->assertEquals('tested', $view->test);
        $this->assertEquals(null, $view['test']);
    }

    /**
     * @test
     */
    function value_gets_value_from_old_inputs()
    {
        $inputs = [
            'text' => 'tested',
            'html' => '<bold>',
            'more' => [
                'check' => 'done',
                'html' => '<italic>',
            ],
        ];
        $data = ['inputs' => $inputs];
        $view = new View($data);

        // getting values
        $this->assertEquals('tested', $view->value('text'));
        $this->assertEquals('done', $view->value('more[check]'));
        $this->assertEquals('<italic>', $view->value('more[html]'));
        $this->assertEquals('&lt;italic&gt;', $view->valueSafe('more[html]'));
    }

    /**
     * @test
     */
    function value_gets_value_from_inputs_and_data()
    {
        $inputs = [
            'text' => 'quality',
            'more' => [
                'html' => '<div>',
            ],
        ];
        $data = [
            'inputs' => $inputs,
            'text' => 'tested',
            'html' => '<bold>',
            'more' => [
                'check' => 'done',
                'html' => '<italic>',
            ],
        ];
        $view = new View($data);

        // getting values
        $this->assertEquals('quality', $view->value('text'));
        $this->assertEquals('done', $view->value('more[check]'));
        $this->assertEquals('<div>', $view->value('more[html]'));
        $this->assertEquals('&lt;div&gt;', $view->valueSafe('more[html]'));
    }
}
