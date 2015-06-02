<?php
namespace Tuum\Web\Middleware;

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
     * @return $this
     */
    public function setRoot($root)
    {
        $this->_patterns[] = $root;
        return $this;
    }

    /**
     * check if the requested path matches with the root. 
     *
     * returns $request if matches, or return false if not matched
     * (i.e. execute the next middleware).
     * 
     * @param Request $request
     * @return bool
     */
    public function matchRoot(&$request)
    {
        // empty means match always.
        if (empty($this->_patterns)) {
            return true;
        }
        $path   = $request->getPathToMatch();
        $method = $request->getMethod();
        foreach ($this->_patterns as $pattern) {
            if ($matched = Matcher::verify($pattern, $path, $method)) {
                if (isset($matched['matched'])) {
                    $request = $request->withPathToMatch($matched['matched'], $matched['trailing']);
                }
                return true;
            }
        }
        return false;
    }
}