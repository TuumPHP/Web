<?php
namespace Tuum\Web\Stack;

use Tuum\Locator\CommonMark;
use Tuum\Locator\LocatorInterface;
use Tuum\Web\Middleware\BeforeFilterTrait;
use Tuum\Web\Middleware\MatchRootTrait;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class DocView implements MiddlewareInterface
{
    use MiddlewareTrait;

    use MatchRootTrait;

    use BeforeFilterTrait;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var CommonMark
     */
    private $markUp;

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
        'txt'  => 'text/plain',
        'text' => 'text/plain',
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
        'php'  => 'evaluatePhp',
        'md'   => 'markToHtml',
        'txt'  => 'textToPre',
        'text' => 'textToPre',
    ];

    /**
     * @param LocatorInterface $locator
     */
    public function __construct($locator, $mark = null)
    {
        $this->locator = $locator;
        $this->markUp  = $mark;
    }

    /**
     * @param Request       $request
     * @param callable|null $next
     * @return null|Response
     */
    public function __invoke($request, $next = null)
    {
        if (!$matched = $this->isMatch($request)) {
            return $this->execNext($request);
        }
        foreach($this->_beforeFilters as $filter) {
            $filter = $request->getFilter($filter);
            $next = $this->getReturnable();
            $filter($request, $next);
            $request = $next->get($request);
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
            return $this->handleView($request, $path);
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
     * @param Request $request
     * @param string  $path
     * @return null|Response
     */
    private function handleView($request, $path)
    {
        foreach ($this->view_extensions as $ext => $handler) {
            if ($file_loc = $this->locator->locate($path . '.' . $ext)) {
                $info = [
                    'loc' => $file_loc,
                    'path' => $path,
                    'ext' => $ext,
                ];
                return $this->$handler($request, $info);
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @param array   $info
     * @return null|Response
     */
    private function evaluatePhp($request, array $info)
    {
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include $info['loc'];
        return $request->respond()->asContents(ob_get_clean());
    }

    /**
     * @param Request $request
     * @param array   $info
     * @return null|Response
     */
    private function markToHtml($request, array $info)
    {
        $path = $info['path'];
        $ext  = $info['ext'];
        if (!$this->markUp) {
            throw new \InvalidArgumentException('no converter for CommonMark file');
        }
        $html = $this->markUp->getHtml($path . '.' . $ext);

        return $request->respond()->asContents($html);
    }

    /**
     * @param Request $request
     * @param array   $info
     * @return null|Response
     */
    private function textToPre($request, array $info)
    {
        $path = $info['path'];
        $ext  = $info['ext'];
        $file_loc = $this->locator->locate($path . '.' . $ext);
        return $request->respond()->asContents('<pre>'.\file_get_contents($file_loc).'</pre>');
    }
    
    /**
     * dummy method to call private methods which are judged as unused methods.
     *
     * @param Request $request
     */
    protected function dummy($request)
    {
        $this->evaluatePhp($request, null);
        $this->markToHtml($request, null, null);
        $this->textToPre($request, null, null);
    }
}