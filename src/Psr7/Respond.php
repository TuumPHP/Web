<?php
namespace Tuum\Web\Psr7;

use Closure;
use Psr\Http\Message\StreamInterface;
use Tuum\Web\View\ErrorView;
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
     * @var ErrorView
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
     * @param ErrorView $view
     */
    public function setErrorViews($view)
    {
        $this->error_views = $view;
    }

    /**
     * @param ViewEngineInterface $view
     */
    public function setViewEngine($view)
    {
        $this->view_engine = $view;
    }

    /**
     * creates a Response with a view file, $file.
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
     * creates a Response of view with given $html as a contents.
     * use this to view a main contents with layout.
     *
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
        if ($this->error_views) {
            $stream = $this->error_views->getStream($status);
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
     * creates a response of file contents.
     * A file can be a string of the file's pathName, or a file resource.
     *
     * @param string|resource $file_loc
     * @param string $mime
     * @return Response
     */
    public function asFileContents($file_loc, $mime)
    {
        $stream = StreamFactory::file($file_loc);
        return new Response($stream, self::OK, ['Content-Type' => $mime]);
    }

    /**
     * creates a response for downloading a contents.
     * A contents can be, a text string, a resource, or a stream.
     *
     * @param string|StreamInterface|resource      $content
     * @param string      $filename
     * @param bool        $attach      download as attachment if true, or inline if false. 
     * @param string|null $mime
     * @return Response
     */
    public function asDownload($content, $filename, $attach=true, $mime=null)
    {
        $type = $attach ? 'attachment' : 'inline';
        $response = new Response(
            StreamFactory::make($content),
            self::OK, [
            'Content-Disposition' => "{$type}; filename=\"{$filename}\"",
            'Content-Length'      => strlen($content),
            'Content-Type'        => $mime ?: 'application/octet-stream',
            'Cache-Control'       => 'public', // for IE8
            'Pragma'              => 'public', // for IE8
        ])
        ;
        return $response;
    }

    /**
     * creates a generic response.
     *
     * @param string|StreamInterface|resource       $input
     * @param string $status
     * @param array  $header
     * @return Response
     */
    public function asResponse($input, $status=self::OK, $header=[])
    {
        return new Response(
            StreamFactory::make($input),
            $status,
            $header
        );
    }

    /**
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