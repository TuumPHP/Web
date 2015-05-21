<?php
namespace Tuum\Web\Stack;

use Tuum\Locator\CommonMark;
use Tuum\Locator\Locator;
use Tuum\Locator\LocatorInterface;
use Tuum\Web\Middleware\AfterReleaseTrait;
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
    
    use AfterReleaseTrait;

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
        'css'  => 'text/css',
        'js'   => 'text/javascript',
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
     * @param null|CommonMark  $mark
     */
    public function __construct($locator, $mark = null)
    {
        $this->locator = $locator;
        $this->markUp  = $mark;
    }

    /**
     * @param string $docs_dir
     * @param string $vars_dir
     * @return DocView
     */    
    public static function forge($docs_dir, $vars_dir)
    {
        return new DocView(
            new Locator($docs_dir),
            CommonMark::forge(
                $docs_dir,
                $vars_dir . '/markUp')
        );

    }

    /**
     * @param Request       $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        // matches requested path with the root.
        if (!$this->matchRoot($request)) {
            return $this->next ? $this->next->__invoke($request) : null;
        }
        
        // apply before filter. 
        list($request, $response) = $this->filterBefore($request);
        if ($response) {
            return $response;
        }

        if ($response = $this->handle($request)) {
            return $response;
        }

        return $this->next ? $this->next->__invoke($request) : null;
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
            $response = $this->handleView($request, $path);
        } else {
            $response = $this->handleEmit($request, $path, $ext);
        }
        return $this->applyAfterReleases($request, $response);
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

        return $request->respond()->asFileContents($file_loc, $mime);
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
     * @param array $options
     */
    public function options(array $options)
    {
        if (array_key_exists('enable_raw', $options)) {
            $this->enable_raw = (bool) $options['enable_raw'];
        }
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
        $this->evaluatePhp($request, []);
        $this->markToHtml($request, []);
        $this->textToPre($request, []);
    }
}