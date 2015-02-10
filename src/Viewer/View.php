<?php
namespace Tuum\Web\Viewer;

use Psr\Http\Message\UriInterface;
use Traversable;

/**
 * Class View
 *
 * @property Errors errors
 * @property Inputs inputs
 * @property Message message
 * @package Tuum\View
 */
class View implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var Inputs
     */
    public $values;

    /**
     * @var array
     */
    protected $_data_ = [];

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
    public $uri;

    /**
     * a callable to escape a string.
     *
     * @var callable
     */
    public $escape = 'h';

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
        $this->escape = [$this, 'htmlSafe'];
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
     * returns new view object populated with its _data_[$key].
     *
     * @param string $key
     * @return View
     */
    public function withKey($key)
    {
        $data = $this->get($key);
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
        $this->inputs  = new Inputs($this->bite($data, 'inputs'));
        $this->errors  = new Errors($this->bite($data, 'errors'));
        $this->message = new Message($this->bite($data, 'messages'));
        $this->uri     = new Message($this->bite($data, 'uri'));
        $this->values  = new Inputs($data);
        $this->_data_  = $data;
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

    /**
     * escapes a string using $this->escape.
     *
     * @param string $string
     * @return mixed
     */
    protected function escape($string)
    {
        if(is_string($string)) {
            return call_user_func($this->escape, $string);
        }
        return $string;
    }

    /**
     * escape for html output.
     *
     * @param string $string
     * @return string
     */
    protected function htmlSafe($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * a handy method to escape a string as input.
     *
     * @param string $string
     * @return string
     */
    public function e($string)
    {
        return $this->escape($string);
    }

    // +----------------------------------------------------------------------+
    //  values from old input or current data.
    // +----------------------------------------------------------------------+
    /**
     * search for inputs (old input from previous post), and then
     * values passed from a controller.
     *
     * @param string $key
     * @return array|mixed|null|string
     */
    public function value($key)
    {
        if($found = $this->inputs->get($key)) {
            return $found;
        }
        return $this->values->get($key);
    }

    /**
     * same as value method, but strings are escaped for html.
     *
     * @param string $key
     * @return array|mixed|null|string
     */
    public function valueSafe($key)
    {
        $found = $this->value($key);
        if( is_string($found)) {
            return $this->escape($found);
        }
        return $found;
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
        return $this->get($key);
    }

    /**
     * get raw value.
     *
     * @param string       $key
     * @param null|mixed   $default
     * @return mixed
     */
    function get($key, $default=null)
    {
        if (!$this->offsetExists($key)) {
            return $default;
        }
        if(is_array($this->_data_) || $this->_data_ instanceof \ArrayAccess) {
            return $this->_data_[$key];
        }
        if(is_object($this->_data_)) {
            return $this->_data_->$key;
        }
        throw new \RuntimeException('unknown view data given');
    }

    /**
     * get keys of current data (if it is an array).
     *
     * @return array
     */
    public function getKeys()
    {
        return is_array($this->_data_) ? array_keys($this->_data_) : [];
    }

    /**
     * get escaped value.
     *
     * @param string       $key
     * @return string
     */
    function safe($key)
    {
        $html = $this->get($key);
        if(is_string($html)) {
            $html = $this->escape($html);
        }
        return $html;
    }

    /**
     * get value as hidden tag with $key as name.
     *
     * @param string $key
     * @return string
     */
    public function hiddenTag($key)
    {
        if ($this->offsetExists($key)) {
            $value = $this->safe($key);
            return "<input type='hidden' name='{$key}' value='{$value}' />";
        }
        return '';
    }

    // +----------------------------------------------------------------------+
    //  for ArrayAccess and Iterator
    // +----------------------------------------------------------------------+
    /**
     * check if the $offset exists in the data.
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        if(is_array($this->_data_) || $this->_data_ instanceof \ArrayAccess) {
            return array_key_exists($offset, $this->_data_);
        }
        if(is_object($this->_data_)) {
            if(isset($this->_data_->$offset)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Offset to retrieve. automatically escapes the output.
     *
     * @param mixed $offset 
     * @return mixed 
     */
    public function offsetGet($offset)
    {
        return $this->escape($this->get($offset, null));
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('cannot set new values in View');
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset 
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('cannot unset a value from View');
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable 
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_data_);
    }
}