<?php
namespace Tuum\Web\Middleware;

use Closure;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;

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
     * @return ReturnRequest
     */
    abstract protected function getReturnable();
        
    /**
     * @param string|Closure|ApplicationInterface $filter
     */
    public function setBeforeFilter($filter)
    {
        $this->_beforeFilters[] = $filter;
    }

    /**
     * @param Request $request
     * @return array [Request, Response]
     */
    protected function filterBefore($request)
    {
        if (empty($this->_beforeFilters)) {
            return [$request, null];
        }
        $response = null;
        $retReq   = $this->getReturnable();
        foreach ($this->_beforeFilters as $filter) {
            if (!$filter = $request->getFilter($filter)) {
                continue;
            }
            $response = $filter($request, $retReq);
            $request  = $retReq->get($request);
            if ($response) {
                return [$request, $response];
            }
        }
        return [$request, $response];
    }
}