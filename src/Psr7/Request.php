<?php
namespace Tuum\Web\Psr7;

use Phly\Http\ServerRequest;

class Request extends ServerRequest
{
    /**
     * @var Respond
     */
    protected $respond;

    public function __construct(
        array $serverParams = [],
        array $fileParams = [],
        $uri = null,
        $method = null,
        $body = 'php://input',
        array $headers = []
    ) {
        $this->respond = new Respond($this);
        return parent::__construct($serverParams, $fileParams, $uri, $method, $body, $headers);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->respond->with($key, $value);
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setAttributes($data)
    {
        $this->respond->with($data);
        return $this;
    }

    /**
     * @return Respond
     */
    public function respond()
    {
        return $this->respond;
    }
}