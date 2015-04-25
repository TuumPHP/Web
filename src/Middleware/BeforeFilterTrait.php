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
     * @param Closure $nextReq
     * @return null|Response
     */
    protected function applyBeforeFilters($request, $nextReq)
    {
        foreach ($this->_beforeFilters as $filter) {
            if (!$filter = $request->getFilter($filter)) {
                continue;
            }
            $retReq = $this->getReturnable();
            if ($response = $filter($request, $retReq)) {
                return $response;
            }
            $request = $retReq->get($request);
        }
        $nextReq($request);
        return null;
    }

}