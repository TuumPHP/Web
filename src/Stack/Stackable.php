<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\App\AppMarkerInterface;
use Tuum\Web\App\AppReleaseInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

/**
 * Class Pile
 *
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
     * @var AppMarkerInterface
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
     * @param AppMarkerInterface $app
     */
    public function __construct(AppMarkerInterface $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return null|Response
     */
    public function execute($request, $response)
    {
        /*
         * first, check for match and before filters. 
         */
        if (!$this->isMatch($request)) {
            // if not matched, ignore this middleware and execute the next handler
            return $this->execNext($request, $response);
        }
        if (!$response) {
            // apply filters, if $response is not set. 
            $response = $this->applyBeforeFilters($request);
        }
        /*
         * now, run the AppHandle/AppRelease..
         */
        $app = $this->app;
        if (!$response && ( $app instanceof AppHandleInterface ) ) {
            // AppHandleInterface: execute the handler if $response is not set yet.
            $response = $app->__invoke($request);
        }
        if ($app instanceof AppReleaseInterface) {
            // AppReleaseInterface: execute the handler, always.
            $response = $app->__invoke($request, $response);
        }
        // execute next handler, always.
        $response = $this->execNext($request, $response);

        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    protected function execNext($request, $response)
    {
        // execute the next handler.
        if ($this->next) {
            return $this->next->execute($request, $response);
        }
        return $response;
    }

    /**
     * @param AppMarkerInterface|StackableInterface $handler
     * @return StackableInterface|static
     */
    public static function makeStack($handler)
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
     * @param AppMarkerInterface $handler
     * @return $this
     */
    public function push(AppMarkerInterface $handler)
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