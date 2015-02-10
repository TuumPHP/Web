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
    function withData_returns_new_view_object()
    {
        $view1 = new View(['test'=>'testing']);
        $view2 = $view1->withData(['test'=>'tested']);
        $this->assertEquals('testing', $view1->data->test);
        $this->assertEquals('tested', $view2->data->test);
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
        $esc = View::$escape;
        View::$escape = 'addslashes';
        $this->assertEquals('<bold>', $view->e('<bold>'));
        $this->assertEquals('a\\\'b', $view->e('a\'b'));
        View::$escape = $esc;
    }

}
