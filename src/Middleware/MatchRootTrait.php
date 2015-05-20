<?php
namespace Tuum\Web\Middleware;

use Closure;
use Tuum\Router\Matcher;
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
     * @deprecated
     * @param Request  $request
     * @param Closure $reqRet
     * @return bool|array
     */
    public function isMatch($request, $reqRet)
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
            if ($matched = Matcher::verify($pattern, $path, $method)) {
                if (isset($matched['matched'])) {
                    $reqRet($request->withPathToMatch($matched['matched'], $matched['trailing']));
                }
                return $matched;
            }
        }
        return false;
    }

    /**
     * check if the requested path matches with the root. 
     *
     * returns $request if matches, or return false if not matched
     * (i.e. execute the next middleware).
     * 
     * @param Request $request
     * @return Request|bool
     */
    public function matchRoot($request)
    {
        // empty means match always.
        if (empty($this->_patterns)) {
            return $request;
        }
        $path   = $request->getPathToMatch();
        $method = $request->getMethod();
        foreach ($this->_patterns as $pattern) {
            if ($matched = Matcher::verify($pattern, $path, $method)) {
                if (isset($matched['matched'])) {
                    return $request->withPathToMatch($matched['matched'], $matched['trailing']);
                }
                return $request;
            }
        }
        return false;

    }
}