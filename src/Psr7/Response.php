<?php
namespace Tuum\Web\Psr7;

use Phly\Http\Response as BaseResponse;
use Psr\Http\Message\UriInterface;

class Response extends BaseResponse
{
    const TYPE_VIEW = 'view';
    const TYPE_REDIRECT = 'redirect';
    const TYPE_ERROR = 'error';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $data=[];

    /**
     * @var string
     */
    protected $view_file;

    /**
     * @var string
     */
    protected $redirect_to;

    /**
     * @param string $type
     * @return bool
     */
    public function isType($type)
    {
        return $this->type === $type;
    }

    /**
     * @param null|string $key
     * @return array
     */
    public function getData($key=null)
    {
        if(!is_null($key)) {
            return array_key_exists($key, $this->data) ? $this->data[$key] : null;
        }
        return $this->data;
    }

    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->view_file;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->getHeader('Location');
    }

    /**
     * @param string $file
     * @param array  $data
     * @return Response
     */
    public static function view($file, $data=[])
    {
        $self = new self;
        $self->view_file = $file;
        $self->data = $data;
        $self->type = self::TYPE_VIEW;
        return $self;
    }

    /**
     * @param string|UriInterface $uri
     * @param array  $data
     * @return Response
     */
    public static function redirect($uri, $data=[])
    {
        if($uri instanceof UriInterface) {
            $uri = (string) $uri;
        }
        $self = new self('php://memory', '302', ['Location' => $uri ]);
        $self->redirect_to = $uri;
        $self->data = $data;
        $self->type = self::TYPE_REDIRECT;
        return $self;
    }

    /**
     * @param int   $status
     * @param array $data
     * @return Response
     */
    public static function error($status, $data=[])
    {
        $self = new self('php://memory', $status);
        $self->data = $data;
        $self->type = self::TYPE_ERROR;
        return $self;
    }

    /**
     * send back the headers (if not sent) and body.
     */
    public function send()
    {
        if(!headers_sent()) {
            $this->sendHeaders();
        }
        echo $this->getBody();
    }

    /**
     *
     */
    private function sendHeaders()
    {
        if ($this->getReasonPhrase()) {
            header(sprintf(
                'HTTP/%s %d %s',
                $this->getProtocolVersion(),
                $this->getStatusCode(),
                $this->getReasonPhrase()
            ));
        } else {
            header(sprintf(
                'HTTP/%s %d',
                $this->getProtocolVersion(),
                $this->getStatusCode()
            ));
        }

        foreach ($this->getHeaders() as $header => $values) {
            $name  = $this->filterHeader($header);
            $first = true;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first);
                $first = false;
            }
        }
    }

    /**
     * Filter a header name to wordcase
     *
     * @param string $header
     * @return string
     */
    private function filterHeader($header)
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);
        return str_replace(' ', '-', $filtered);
    }
}