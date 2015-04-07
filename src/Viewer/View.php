<?php
namespace Tuum\Web\Viewer;

use Psr\Http\Message\UriInterface;
use Tuum\Web\View\Value;

if(!function_exists('Tuum\Web\Viewer\htmlSafe')) {
    function htmlSafe($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
/**
 * Class View
 *
 * @property Errors errors
 * @property Inputs inputs
 * @property Message message
 * @property Data    data
 * @property UriInterface     uri
 * @package Tuum\View
 */
class View
{
    /**
     * @var Inputs
     */
    public $values;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var Inputs
     */
    protected $inputs;

    /**
     * @var Errors
     */
    protected $errors;

    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * a callable to escape a string.
     *
     * @var callable
     */
    public static $escape = 'Tuum\Web\Viewer\htmlSafe';

    // +----------------------------------------------------------------------+
    //  construction
    // +----------------------------------------------------------------------+
    /**
     * @ param Message $message
     *
     * @param array|object $data
     */
    public function __construct($data=[])
    {
        $this->setData($data);
    }

    /**
     * creates a new View object with $data set.
     *
     * @param array|object $data
     * @return View
     */
    public function withData($data)
    {
        $view = clone($this);
        $view->setData($data);
        return $view;
    }

    /**
     * populates View with data.
     *
     * @param array|object $data
     */
    protected function setData($data)
    {
        if(empty($data)) return;
        $this->inputs  = new Inputs($this->bite($data, Value::INPUTS));
        $this->errors  = new Errors($this->bite($data, Value::ERRORS));
        $this->message = new Message($this->bite($data, Value::MESSAGE));
        $this->uri     = new Message($this->bite($data, Value::URI));
        $this->values  = new Inputs($data);
        $this->data    = new Data($data);
    }

    /**
     * a way to populate data.
     *
     * @param array  $data
     * @param string $key
     * @return array
     */
    private function bite(&$data, $key)
    {
        if(is_array($data) && array_key_exists($key, $data) && is_array($data[$key])) {
            $found = $data[$key];
            unset($data[$key]);
            return $found;
        }
        return [];
    }

    // +----------------------------------------------------------------------+
    //  values from current data.
    // +----------------------------------------------------------------------+
    /**
     * accessing its internal properties.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if(isset($this->$key)) {
            return $this->$key;
        }
        return null;
    }
    
    /**
     * escapes a string using $this->escape.
     *
     * @param string $string
     * @return mixed
     */
    public static function escape($string)
    {
        if(is_string($string)) {
            $func = self::$escape;
            return $func($string);
        }
        return $string;
    }

    /**
     * a handy method to escape a string as input.
     *
     * @param string $string
     * @return string
     */
    public function e($string)
    {
        return self::escape($string);
    }

    /**
     * escape for html output.
     *
     * @param string $string
     * @return string
     */
    protected static function htmlSafe($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

}