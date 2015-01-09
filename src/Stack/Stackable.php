<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

/**
 * Class Pile
 * @package WScore\Pile
 *
 * creates a pile of handlers for http request.
 * continues processing the request until one of the pile returns a response.
 */
class Stackable implements StackableInterface
{
    /**
     * the middleware. the Http Kernel that does the job.
     *
     * @var WebHandleInterface|AbstractStack
     */
    protected $middleware;

    /**
     * pile of Stackable Http Kernels.
     *
     * @var Stackable
     */
    protected $next;

    /**
     * wraps the Http Kernel that does the job with Stackable Http Kernel.
     *
     * @param WebHandleInterface $middleware
     */
    public function __construct(WebHandleInterface $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function handle($request)
    {
        if (!$this->isMatch($request)) {
            if ($this->next) {
                return $this->next->handle($request);
            }
            return null;
        }
        if ($response = $this->applyBeforeFilters($request)) {
            return $response;
        }
        return $this->_handle($request);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function _handle($request)
    {
        // get the response from the own handler.
        $response = $this->middleware->handle($request);

        // if no response, invoke the next pile of handler.
        if (!$response && $this->next) {
            $response = $this->next->handle($request);
        }
        // process the response if PileInterface is implemented.
        if ($this->middleware instanceof WebReleaseInterface) {
            $response = $this->middleware->release($request, $response);
        }
        return $response;
    }

    /**
     * @param WebHandleInterface $handler
     * @return StackableInterface|static
     */
    public static function makeStack(WebHandleInterface $handler)
    {
        if (!$handler instanceof StackableInterface) {
            $handler = new static($handler);
        }
        return $handler;
    }

    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param WebHandleInterface $handler
     * @return $this
     */
    public function push(WebHandleInterface $handler)
    {
        if ($this->next) {
            return $this->next->push($handler);
        }
        $this->next = static::makeStack($handler);
        return $this->next;
    }

    /**
     * overwrite this method.
     *
     * @param Request $request
     * @return bool
     */
    protected function isMatch($request)
    {
        if (method_exists($this->middleware, 'isMatch')) {
            return $this->middleware->isMatch($request);
        }
        return true;
    }

    /**
     * overwrite this method.
     *
     * @param Request $request
     * @return null
     */
    protected function applyBeforeFilters($request)
    {
        if (method_exists($this->middleware, 'filterBefore')) {
            return $this->middleware->filterBefore($request);
        }
        return null;
    }
}