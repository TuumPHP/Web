<?php
namespace Tuum\Web\Psr7;

use Phly\Http\Stream;
use Psr\Http\Message\UriInterface;

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
     * @var Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @param null|string $key
     * @return array|mixed
     */
    public function get($key=null)
    {
        if(is_null($key)) {
            return $this->data;
        }
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
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
     * @param array $input
     * @return Respond
     */
    public function withInput(array $input)
    {
        return $this->with('inputs', $input);
    }

    /**
     * @param string $message
     * @return Respond
     */
    public function withMessage($message)
    {
        return $this->with('message', [
            'message' => $message
        ]);
    }

    /**
     * @param string $message
     * @return Respond
     */
    public function withErrorMessage($message)
    {
        return $this->with('message', [
            'message' => $message,
            'error'   => true,
        ]);
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
        return Response::view($file, $this->data);
    }

    /**
     * returns a string as a plain text.
     *
     * @param string $text
     * @return Response
     */
    public function asText($text)
    {
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($text);
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
        $stream = new Stream('php://memory', 'wb+');
        $stream->write(json_encode($data));
        return new Response($stream, self::OK, ['Content-Type' => 'application/json']);
    }

    /**
     * redirects to $uri.
     * the $uri must be a full uri (like http://...), or a UriInterface object.
     *
     * @param UriInterface|string $uri
     * @return Response
     */
    public function asRedirectUri($uri)
    {
        return Response::redirect($uri, $this->data);
    }

    /**
     * redirects to a path in string.
     * uses current hosts and scheme.
     *
     * @param string $path
     * @return Response
     */
    public function asPath($path)
    {
        $uri = $this->request->getUri()->withPath($path);
        return $this->asRedirectUri($uri);
    }

    /**
     * return a response with error number.
     *
     * @param int|string $status
     * @return Response
     */
    public function asError($status=self::INTERNAL_ERROR)
    {
        return Response::error($status, $this->data);
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
}