<?php
namespace Tuum\Web\Stack;

use Closure;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

/**
 * Class StackFilterTrait
 * @package Tuum\Web\Stack
 *
 * apply filters if matched, but before the main handle.
 */
trait StackFilterTrait
{
    /**
     * list of filters to apply if matched.
     *
     * @var string[]|Closure[]|StackHandleInterface[]
     */
    protected $_beforeFilters = [];

    /**
     * @param string|Closure|StackHandleInterface $filter
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
            if ($response = $request->filter($filter)) {
                return $response;
            }
        }
        return null;
    }

}