<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Http\Request;

/**
 * Class StackFilterTrait
 * @package Tuum\Web\Stack
 *
 * a trait for a simple match list of roots against path-info.
 */
trait StackMatchTrait
{
    /**
     * matching conditions; list of paths.
     *
     * @var string[]
     */
    protected $_roots = [];

    /**
     * @param string $root
     */
    public function setRoot($root)
    {
        $this->_roots[] = $root;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isMatch($request)
    {
        // empty means match always.
        if (empty($this->_roots)) {
            return true;
        }
        /*
         * match roots against the path info.
         */
        $path = $request->getPathInfo();
        foreach ($this->_roots as $root) {
            if( stripos($path, $root)===0) {
                $request->updatePath($root);
                return true;
            }
        }
        return false;
    }

}