<?php
namespace Tuum\Web\Psr7;

use Closure;
use Tuum\Web\Application;
use Tuum\Web\View\ViewEngineInterface;
use Tuum\Web\View\ViewStream;

/**
 * Class Respond
 *
 * @package Tuum\Web\Psr7
 *
 * responds a new Request object with data.
 *
 * $respond->with('yes', 'no')->asView('template.file');
 */
class Respond extends AbstractResponseFactory
{
    const OK = '200';
    const FILE_NOT_FOUND = '404';
    const UNAUTHORIZED = '401';
    const ACCESS_DENIED = '403';
    const INTERNAL_ERROR = '500';

    /**
     * @var array
     */
    public $error_views;

    /**
     * @var ViewEngineInterface
     */
    private $view_engine;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param array $views
     */
    public function setErrorViews(array $views = [])
    {
        $this->error_views = $views;
    }

    /**
     * @param ViewEngineInterface $view
     */
    public function setViewEngine($view)
    {
        $this->view_engine = $view;
    }

    /**
     * return from a view file, $file.
     * rendering must occur on the way back.
     *
     * @param string|Closure $file
     * @return Response
     */
    public function asView($file)
    {
        if ($app = $this->request->getWebApp()) {
            $stream = $this->forgeStreamView($file, $this->data);
            return Response::view($stream, $file, $this->data);
        }
        return Response::view('php://memory', $file, $this->data);
    }

    /**
     * @param string $html
     * @return Response
     */
    public function asContents($html)
    {
        $file = function() use($html) {return $html;};
        return $this->asView($file);
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
     * return a response with error number.
     *
     * @param int|string $status
     * @return Response
     */
    public function asError($status = self::INTERNAL_ERROR)
    {
        if ($this->error_views && $app = $this->request->getWebApp()) {
            $view   = isset($this->error_views[$status]) ?$this->error_views[$status]:$this->error_views[0]; 
            $stream = $this->forgeStreamView($view);
        } else {
            $stream = null;
        }
        return Response::error($status, $this->data, $stream);
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

    /**
     * @param string $file_loc
     * @param string $mime
     * @return Response
     */
    public function asResponse($file_loc, $mime)
    {
        $stream = StreamFactory::file($file_loc);
        return new Response($stream, self::OK, ['Content-Type' => $mime]);
    }

    /**
     * @param Application $app
     * @param string      $file
     * @param array       $data
     * @return ViewStream
     */
    private function forgeStreamView($file, $data = [])
    {
        if ($this->view_engine) {
            return $this->view_engine->getStream($file, $data);
        }
        return StreamFactory::string('');
    }
}