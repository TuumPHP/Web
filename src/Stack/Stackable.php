<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AbstractApp;
use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\App\AppReleaseInterface;
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
     * @var AppHandleInterface|AbstractApp
     */
    protected $app;

    /**
     * pile of Stackable Http Kernels.
     *
     * @var Stackable
     */
    protected $next;

    /**
     * wraps the Http Kernel that does the job with Stackable Http Kernel.
     *
     * @param AppHandleInterface $app
     */
    public function __construct(AppHandleInterface $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function execute($request)
    {
        if (!$this->isMatch($request)) {
            if ($this->next) {
                return $this->next->execute($request);
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
        $response = $this->app->handle($request);

        // if no response, invoke the next pile of handler.
        if (!$response && $this->next) {
            $response = $this->next->execute($request);
        }
        // process the response if PileInterface is implemented.
        if ($this->app instanceof AppReleaseInterface) {
            $response = $this->app->release($request, $response);
        }
        return $response;
    }

    /**
     * @param AppHandleInterface $handler
     * @return StackableInterface|static
     */
    public static function makeStack(AppHandleInterface $handler)
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
     * @param AppHandleInterface $handler
     * @return $this
     */
    public function push(AppHandleInterface $handler)
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
        if (method_exists($this->app, 'isMatch')) {
            return $this->app->isMatch($request);
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
        if (method_exists($this->app, 'filterBefore')) {
            return $this->app->filterBefore($request);
        }
        return null;
    }
}