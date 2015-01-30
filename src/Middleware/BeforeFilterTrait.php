<?php
namespace Tuum\Web\Middleware;

use Closure;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

/**
 * Class StackFilterTrait
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
            if ($response = $request->filter($filter)) {
                return $response;
            }
        }
        return null;
    }

}