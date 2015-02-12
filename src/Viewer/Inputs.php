<?php
namespace Tuum\Web\Viewer;

class Inputs
{
    /**
     * @var array
     */
    protected $inputs = [];

    /**
     * @param array $inputs
     */
    public function __construct($inputs = [])
    {
        $this->inputs = $inputs;
    }

    /**
     * @param array $inputs
     */
    public function setInputs($inputs)
    {
        $this->inputs = $inputs;
    }

    /**
     * @param string        $name
     * @param string|null   $option
     * @return array|mixed|string
     */
    public function get($name, $option=null)
    {
        $name = str_replace('[]', '', $name);
        parse_str($name, $levels);
        $inputs = $this->inputs;
        $found = $this->recurseGet($levels, $inputs);
        if(!is_null($found)) {
            return $found;
        }
        return $option;
    }

    /**
     * @param string $name
     * @param string $value
     * @return bool
     */    
    public function exists($name, $value=null)
    {
        $found = $this->get($name);
        if(is_null($found)) return false;
        if(!is_null($value)) {
            if(is_array($found)) {
                return in_array($value, $found);
            }
            return $value == $found;
        }
        return true;
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public function checked($name, $value)
    {
        if($this->exists($name, $value)) {
            return ' checked';
        }
        return '';
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public function selected($name, $value)
    {
        if($this->exists($name, $value)) {
            return ' selected';
        }
        return '';
    }

    /**
     * @param array $levels
     * @param array $inputs
     * @return mixed
     */
    protected function recurseGet($levels, $inputs)
    {
        if (!is_array($levels)) {
            if(is_null($inputs) || $inputs === false) {
                $inputs = '';
            }
            return $inputs;
        }
        list($key, $next) = each($levels);
        // an array
        if (is_array($inputs) && array_key_exists($key, $inputs)) {
            return $this->recurseGet($next, $inputs[$key]);
        }
        // object accessing as ArrayAccess
        if (is_object($inputs) && $inputs instanceof \ArrayAccess && isset($inputs[$key])) {
            return $this->recurseGet($next, $inputs[$key]);
        }
        // object accessing as property
        if (is_object($inputs) && isset($inputs->$key)) {
            return $this->recurseGet($next, $inputs->$key);
        }
        return null;
    }

}