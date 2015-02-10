<?php
namespace Tuum\Web\Viewer;

use Traversable;

/**
 * Class View
 *
 * @property Errors errors
 * @property Inputs inputs
 * @property Message message
 * @package Tuum\View
 */
class Data implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     */
    protected $_data_ = [];

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
     * returns new view object populated with its _data_[$key].
     *
     * @param string $key
     * @return Data
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
        $this->_data_  = $data;
    }

    /**
     * @param $string
     * @return mixed
     */
    public function esc($string)
    {
        return View::escape($string);
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
            $html = View::escape($html);
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
        return View::escape($this->get($offset, null));
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