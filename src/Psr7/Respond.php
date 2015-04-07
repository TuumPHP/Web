<?php
namespace Tuum\Web\Psr7;

use Psr\Http\Message\UriInterface;
use Tuum\Web\View\Value;
use Tuum\Web\View\Message;

/**
 * Class Respond
 *
 * @package Tuum\Web\Psr7
 *
 * responds a new Request object with data.
 *
 * $respond->with('yes', 'no')->asView('template.file');
 */
class Respond
{
    const OK = '200';
    const FILE_NOT_FOUND = '404';
    const UNAUTHORIZED = '401';
    const ACCESS_DENIED = '403';
    const INTERNAL_ERROR = '500';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $helpers = [
        Value::MESSAGE => [],
        Value::INPUTS => [],
        Value::ERRORS => [],
        Value::DATA => [],
    ];

    /**
     * @var Request
     */
    protected $request;

    /**
     * 
     */
    public function __construct()
    {
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $this->helpers[Value::DATA] = $this->data;
        return $this->helpers;
    }

    /**
     * @param string|array $key
     * @param null         $value
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
     * @return $this
     */
    private function withHelper($key, $value)
    {
        $this->helpers[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    private function merge($key, $value)
    {
        if( !isset($this->helpers[$key])) {
            $this->helpers[$key] = [];
        }
        $this->helpers[$key][] = $value;
    }

    /**
     * @param array $input
     * @return Respond
     */
    public function withInput(array $input)
    {
        return $this->withHelper(Value::INPUTS, $input);
    }

    /**
     * @param array $errors
     * @return Respond
     */
    public function withInputErrors(array $errors)
    {
        return $this->withHelper(Value::ERRORS, $errors);
    }

    /**
     * @param string $message
     * @return Respond
     */
    public function withMessage($message)
    {
        $this->merge(Value::MESSAGE, [
            'message' => $message,
            'type' => Message::MESSAGE,
        ]);
        return $this;
    }

    /**
     * @param string $message
     * @return Respond
     */
    public function withNotice($message)
    {
        $this->merge(Value::MESSAGE, [
            'message' => $message,
            'type' => Message::ALERT,
        ]);
        return $this;
    }

    /**
     * @param string $message
     * @return Respond
     */
    public function withError($message)
    {
        $this->merge(Value::MESSAGE, [
            'message' => $message,
            'type' => Message::ERROR,
        ]);
        return $this;
    }

    /**
     * return from a view file, $file.
     * rendering must occur on the way back.
     *
     * @param $file
     * @return Response
     */
    public function asView($file)
    {
        return Response::view($file, $this->getAll());
    }

    /**
     * returns a string as a plain text.
     *
     * @param string $text
     * @return Response
     */
    public function asHtml($text)
    {
        $stream = StreamFactory::string($text);
        return new Response($stream, self::OK, ['Content-Type' => 'text/html']);
    }

    /**
     * returns a string as a plain text.
     *
     * @param string $text
     * @return Response
     */
    public function asText($text)
    {
        $stream = StreamFactory::string($text);
        return new Response($stream, self::OK, ['Content-Type' => 'text/plain']);
    }

    /**
     * returns a JSON string of $data array.
     *
     * @param array $data
     * @return Response
     */
    public function asJson(array $data)
    {
        $stream = StreamFactory::string(json_encode($data));
        return new Response($stream, self::OK, ['Content-Type' => 'application/json']);
    }

    /**
     * redirects to $uri.
     * the $uri must be a full uri (like http://...), or a UriInterface object.
     *
     * @param UriInterface|string $uri
     * @return Response
     */
    public function toAbsoluteUri($uri)
    {
        return Response::redirect($uri, $this->getAll());
    }

    /**
     * redirects to a path in string.
     * uses current hosts and scheme.
     *
     * @param string $path
     * @return Response
     */
    public function toPath($path)
    {
        $uri = $this->request->getUri()->withPath($path);
        return $this->toAbsoluteUri($uri);
    }

    /**
     * @param string $path
     * @return Response
     */
    public function toBasePath($path='')
    {
        $path = '/' . ltrim($path, '/');
        $path = $this->request->getBasePath() . $path;
        $uri = $this->request->getUri()->withPath($path);
        return $this->toAbsoluteUri($uri);
    }

    /**
     * return a response with error number.
     *
     * @param int|string $status
     * @return Response
     */
    public function asError($status=self::INTERNAL_ERROR)
    {
        return Response::error($status, $this->getAll());
    }

    /**
     * return a not-found error response.
     *
     * @return Response
     */
    public function asNotFound()
    {
        return $this->asError(self::FILE_NOT_FOUND);
    }

    /**
     * forbidden, or access-denied.
     * use for password failure, or CsRf token failure.
     *
     * @return Response
     */
    public function asForbidden()
    {
        return $this->asError(self::ACCESS_DENIED);
    }
}