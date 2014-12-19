<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

trait StackableTrait
{
    /**
     * the middleware. the Http Kernel that does the job.
     *
     * @var StackHandleInterface
     */
    protected $middleware;

    /**
     * pile of Stackable Http Kernels.
     *
     * @var Stack
     */
    protected $next;

    /**
     * @var array
     */
    protected $beforeFilters = [];

    abstract protected function isMatch($request);

    abstract protected function applyFilters($request);

    /**
     * wraps the Http Kernel that does the job with Stackable Http Kernel.
     *
     * @param StackHandleInterface $middleware
     */
    public function __construct(StackHandleInterface $middleware)
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
        if ($response = $this->applyFilters($request)) {
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
        if ($this->middleware instanceof StackReleaseInterface) {
            $response = $this->middleware->release($request, $response);
        }
        return $response;
    }

    /**
     * @param StackHandleInterface $handler
     * @return StackableInterface|static
     */
    public static function makeStack(StackHandleInterface $handler)
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
     * @param StackHandleInterface $handler
     * @return $this
     */
    public function push(StackHandleInterface $handler)
    {
        if ($this->next) {
            return $this->next->push($handler);
        }
        $this->next = static::makeStack($handler);
        return $this->next;
    }

}