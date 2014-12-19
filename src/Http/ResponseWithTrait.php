<?php
namespace Tuum\Web\Http;

use Symfony\Component\HttpFoundation\Request;

trait ResponseWithTrait
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
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