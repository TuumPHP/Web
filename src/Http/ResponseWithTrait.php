<?php
namespace Tuum\Web\Http;

use Traversable;

trait ResponseWithTrait
{
    /**
     * @var array
     */
    public $data = [];

    /**
     * @param null|string $key
     * @return array
     */
    public function getData($key=null)
    {
        if($key) {
            return array_key_exists($key, $this->data) ? $this->data[$key] : null;
        }
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */    
    public function fill($data)
    {
        if (is_array($data)) {
            $this->data = array_merge( $this->data, $data);
        }
        elseif ($data instanceof Traversable) {
            foreach ($data as $name => $value) {
                $this->data[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function with($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function withMessage($message)
    {
        $this->data['messages'] = ['message' => $message];
        return $this;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function withErrorMsg($error)
    {
        $this->data['messages'] = ['error' => $error];
        return $this;
    }

    /**
     * @param array $input
     * @return $this
     */
    public function withInput($input)
    {
        $this->data['input'] = $input;
        return $this;
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function withValidationMsg($errors)
    {
        $this->data['errors'] = $errors;
        return $this;
    }

}