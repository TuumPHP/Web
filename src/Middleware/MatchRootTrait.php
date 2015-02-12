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
     * check if matches with given roots.
     * returns true/false, but
     * if the matched root has trails, returns new $request with a path to match.
     * 
     * @param Request $request
     * @return bool|Request
     */
    public function isMatch($request)
    {
        // empty means match always.
        if (empty($this->_patterns)) {
            return $request;
        }
        /*
         * match roots against the path info.
         */
        $path   = $request->getPathToMatch();
        $method = $request->getMethod();
        foreach ($this->_patterns as $pattern) {
            if ($matched = Matcher::verify($pattern, $path, $method)) {
                if(isset($matched['matched'])) {
                    return $request->withPathToMatch($matched['matched'], $matched['trailing']);
                }
                return $request;
            }
        }
        return false;
    }

}