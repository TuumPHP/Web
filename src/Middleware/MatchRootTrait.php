<?php
namespace Tuum\Web\Middleware;

use Tuum\Routing\Matcher;
use Tuum\Web\Psr7\Request;

/**
 * Class MatchRootTrait
 *
 * a trait for a middleware to provide a simple root path matching.
 *
 * @package Tuum\Web\Middleware
 */
trait MatchRootTrait
{
    /**
     * matching conditions; list of paths.
     *
     * @var string[]
     */
    protected $_patterns = [];

    /**
     * @param string $root
     */
    public function setRoot($root)
    {
        $this->_patterns[] = $root;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isMatch($request)
    {
        // empty means match always.
        if (empty($this->_patterns)) {
            return true;
        }
        /*
         * match roots against the path info.
         */
        $path   = $request->getPathToMatch();
        $method = $request->getMethod();
        foreach ($this->_patterns as $pattern) {
            if (Matcher::verify($pattern, $path, $method)) {
                return true;
            }
        }
        return false;
    }

}