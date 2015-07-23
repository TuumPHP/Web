<?php
namespace Tuum\Web\Stack;

use Tuum\Locator\FileMap;
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
     * @var FileMap
     */
    private $fileMap;

    /**
     * @param FileMap $fileMap
     */
    public function __construct($fileMap)
    {
        $this->fileMap = $fileMap;
    }

    /**
     * @param string $docs_dir
     * @param string $vars_dir
     * @return DocView
     */    
    public static function forge($docs_dir, $vars_dir)
    {
        return new DocView(
            FileMap::forge($docs_dir, $vars_dir . '/markUp')
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
        $found = $this->fileMap->render($request->getPathToMatch());
        if (empty($found)) {
            return null;
        }
        if (is_resource($found[0])) {
            return $request->respond()->asFileContents($found[0], $found[1]);
        }
        if (is_string($found[0])) {
            return $request->respond()->asContents($found[0]);
        }
        return null;
    }

    /**
     * set up optional behavior.
     *
     * - 'enable_raw' : enables raw view for markdown files.
     * - 'before' : sets before filters.
     * - 'after' : sets after release filters.
     *
     * @param array $options
     */
    public function options(array $options)
    {
        if (array_key_exists('enable_raw', $options)) {
            $this->fileMap->enable_raw = (bool) $options['enable_raw'];
        }
        if (array_key_exists('before', $options)) {
            $this->setBeforeFilter($options['before']);
        }
        if (array_key_exists('after', $options)) {
            $this->setAfterRelease($options['after']);
        }
        if (array_key_exists('roots', $options)) {
            foreach($options['roots'] as $root) {
                $this->setRoot($root);
            }
        }
    }
}