<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\App\AppMarkerInterface;
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
    use NextStackTrait;
    
    /**
     * the middleware. the Http Kernel that does the job.
     *
     * @var AppMarkerInterface
     */
    protected $app;

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
        // execute next handler, always.
        $response = $this->execNext($request, $response);

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