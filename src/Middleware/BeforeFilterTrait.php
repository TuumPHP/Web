<?php
namespace Tuum\Web\Middleware;

use Closure;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

/**
 * Class StackFilterTrait
 *
 * @package Tuum\Web\Stack
 *
 * apply filters if matched, but before the main handle.
 */
trait BeforeFilterTrait
{
    /**
     * list of filters to apply if matched.
     *
     * @var string[]|Closure[]|ApplicationInterface[]
     */
    protected $_beforeFilters = [];

    /**
     * prepends a new middleware/application at the
     * beginning of the stack. returns the prepended stack.
     *
     * @param ApplicationInterface $handler
     * @return MiddlewareInterface
     */
    abstract public function prepend($handler);

    /**
     * @param string|Closure|ApplicationInterface $filter
     */
    public function setBeforeFilter($filter)
    {
        $this->_beforeFilters[] = $filter;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function filterBefore($request)
    {
        foreach ($this->_beforeFilters as $filter) {
            $filter = $request->getFilter($filter);
            $this->prepend($filter);
        }
        return null;
    }

}