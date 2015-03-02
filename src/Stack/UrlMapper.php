<?php
namespace Tuum\Web\Stack;

use Tuum\Locator\LocatorInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;

class UrlMapper implements MiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var array
     */
    public $handlers = [
        'raw'  => ['contents', 'asText', true],
        'html' => ['contents', 'asHtml'],
        'text' => ['contents', 'asText'],
        'txt'  => ['contents', 'asText'],
        'php'  => ['view', 'asView'],
    ];

    /**
     * @param LocatorInterface $locator
     * @param array            $handler
     */
    public function __construct($locator, $handler = [])
    {
        $this->locator  = $locator;
        $this->handlers = array_merge($handler, $this->handlers);
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function __invoke($request)
    {
        $path    = $request->getUri()->getPath();
        $handler = $this->findHandle($path);
        if (!$handler) {
            return $this->execNext($request);
        }
        $file = $this->locatePath($path, $handler);
        if (!$file) {
            return $this->execNext($request);
        }
        $handler['file'] = $file;
        $method          = $handler[0];
        /*
         * execute the handler method.
         * write down them just to avoid unused private method warning.
         */
        if ($method === 'contents') {
            return $this->renderContents($request, $handler);
        }
        if ($method === 'view') {
            return $this->renderView($request, $handler);
        }
        if (is_callable($method)) {
            return $method($request, $handler);
        }
        return $this->$method($request, $handler);
    }

    /**
     * @param string $path
     * @return array
     */
    private function findHandle($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (!$ext) {
            return []; // must have an extension.
        }
        if (!array_key_exists($ext, $this->handlers)) {
            return []; // must have an handler.
        }
        $handler              = $this->handlers[$ext];
        $handler['extension'] = $ext;
        return $handler;
    }

    /**
     * locate a path from document directory.
     *
     * @param string $path
     * @param array  $handler
     * @return bool|string
     */
    private function locatePath($path, $handler)
    {
        if (isset($handler[2]) && $handler[2]) {
            $ext  = strlen($handler['extension']) + 1; // set by findHandle method.
            $path = substr($path, 0, -$ext);
        }
        return $this->locator->locate($path);
    }

    /**
     * @param Request $request
     * @param array   $handler
     * @return Response
     */
    private function renderContents($request, $handler)
    {
        $file = $handler['file'];
        $as   = $handler[1]; // asXYZ
        return $request->respond()->$as(file_get_contents($file));
    }

    /**
     * @param Request $request
     * @param array   $handler
     * @return Response
     */
    private function renderView($request, $handler)
    {
        $path = substr($handler['path'], 0, -4);
        $as   = $handler[1]; // asXYZ
        return $request->respond()->$as($path);
    }
}
