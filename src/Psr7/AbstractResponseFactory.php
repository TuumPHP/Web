<?php
namespace Tuum\Web\Psr7;

use Tuum\Form\Data\Message;
use Tuum\Web\View\Value;

abstract class AbstractResponseFactory
{
    /**
     * @var array
     */
    protected $data = [
        Value::MESSAGE => [],
        Value::INPUTS  => [],
        Value::ERRORS  => [],
    ];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     * @return $this
     */
    public function withRequest($request)
    {
        $self = clone($this);
        $self->request = $request;
        $self->with('basePath', $request->getBasePath());
        return $self;
    }

    /**
     * @param null|string $key
     * @return array|mixed
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    /**
     * @param string|array $key
     * @param mixed        $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        }
        if (is_string($key)) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    protected function merge($key, $value)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        $this->data[$key][] = $value;
    }

    /**
     * @param array $input
     * @return $this
     */
    public function withInput(array $input)
    {
        return $this->with(Value::INPUTS, $input);
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function withInputErrors(array $errors)
    {
        return $this->with(Value::ERRORS, $errors);
    }

    /**
     * @param string $message
     * @return $this
     */
    public function withMessage($message)
    {
        $this->merge(Value::MESSAGE, [
            'message' => $message,
            'type'    => Message::MESSAGE,
        ]);
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function withNotice($message)
    {
        $this->merge(Value::MESSAGE, [
            'message' => $message,
            'type'    => Message::ALERT,
        ]);
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function withError($message)
    {
        $this->merge(Value::MESSAGE, [
            'message' => $message,
            'type'    => Message::ERROR,
        ]);
        return $this;
    }


}