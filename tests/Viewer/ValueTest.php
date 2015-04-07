<?php
namespace tests\Viewer;

use Tuum\Web\Psr7\Respond;
use Tuum\Web\View\Message;
use Tuum\Web\View\Value;

require_once(dirname(__DIR__).'/autoloader.php');

class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function safe_escapes_and_htmlSafe_as_default()
    {
        $view = new Value();
        $this->assertEquals('&lt;bold&gt;', $view->escape('<bold>'));
        $this->assertEquals('a&#039;b', $view->escape('a\'b'));

        // change escape functions
        $view->setEscape('addslashes');
        $this->assertEquals('<bold>', $view->escape('<bold>'));
        $this->assertEquals('a\\\'b', $view->escape('a\'b'));
    }

    /**
     * @test
     */
    function Respond_returns_correct_array_for_Value()
    {
        $res = new Respond();
        $res->withInput(['input' => 'tested'])
            ->withInputErrors(['errors' => 'tested'])
            ->withMessage('message-tested')
            ->withNotice('notice-tested')
            ->withError('error-tested')
            ->with( ['more' => 'done'])
        ;
        $data = $res->getAll();
        $value = (new Value())->forge($data);
        $this->assertEquals('tested', $value->inputs->raw('input'));
        $this->assertEquals('tested', $value->errors->raw('errors'));
        $this->assertEquals('<div class="alert alert-danger">error-tested</div>', $value->message->onlyOne());
        $value->message->formats = [
            Message::MESSAGE => '<success">%s</success>',
            Message::ALERT   => '<info">%s</info>',
            Message::ERROR   => '<danger">%s</danger>',
        ];
        $this->assertEquals(
            '<success">message-tested</success><info">notice-tested</info><danger">error-tested</danger>',
            (string) $value->message);
        $this->assertEquals('done', $value->data->raw('more'));
    }
}
