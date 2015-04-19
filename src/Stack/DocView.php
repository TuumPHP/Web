<?php
namespace Tuum\Web\Stack;

use Tuum\Locator\LocatorInterface;
use Tuum\Web\Middleware\MatchRootTrait;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class DocView implements MiddlewareInterface
{
    use MiddlewareTrait;

    use MatchRootTrait;
    
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * specify the extension => mime type.
     *
     * @var array
     */
    public $emit_extensions = [
        'pdf'  => 'application/pdf',
        'gif'  => 'image/gif',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'txt'      => 'text/plain',
        'text'     => 'text/plain',
    ];

    /**
     * set to true to allow raw access for text and markdown files.
     * 
     * @var bool
     */
    public $enable_raw = false;
    
    /**
     * raw extensions types. 
     * 
     * @var array
     */
    public $raw_extensions = [
        'md'       => 'text/plain',
        'markdown' => 'text/plain',
    ];
    /**
     * for view/template files.
     * 
     * @var array
     */
    public $view_extensions = [
        'php' => 'evaluatePhp',
        'md'  => 'convertDown',
        'html'
    ];

    /**
     * @param LocatorInterface $locator
     */
    public function __construct($locator)
    {
        $this->locator  = $locator;
    }

    /**
     * @param Request       $request
     * @param callable|null $next
     * @return null|Response
     */
    public function __invoke($request, $next = null)
    {
        if( !$matched = $this->isMatch($request)) {
            return $this->execNext($request);
        }
        if ($response = $this->handle($request)) {
            return $response;
        }
        return $this->execNext($request);
    }

    /**
     * do the quick extension check.
     *
     * @param Request $request
     * @return null|Response
     */
    private function handle($request)
    {
        $path = $request->getUri()->getPath();
        $ext  = pathinfo($path, PATHINFO_EXTENSION);
        if (!$ext) {
            return $this->handleView($path);
        }
        return $this->handleEmit($request, $path, $ext);
    }

    /**
     * @param Request $request
     * @param string  $path
     * @param string  $ext
     * @return null|Response
     */
    private function handleEmit($request, $path, $ext)
    {
        $emitExt = $this->emit_extensions;
        if ($this->enable_raw) {
            $emitExt = array_merge($emitExt, $this->raw_extensions);
        }
        if (!isset($emitExt[$ext])) {
            return null;
        }
        if (!$file_loc = $this->locator->locate($path)) {
            return null;
        }
        $mime = $emitExt[$ext];
        return $request->respond()->asResponse($file_loc, $mime);
    }

    /**
     * @param string $path
     * @return null|Response
     */
    private function handleView($path)
    {
    }
}