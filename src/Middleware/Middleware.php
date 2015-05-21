<?php
namespace Tuum\Web\Middleware;

use Tuum\Web\FilterInterface;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\ReleaseInterface;

/**
 * Class Middleware
 *
 * a generic middleware to handle job by $this->app.
 *
 * @package Tuum\Web\Middleware
 */
class Middleware implements MiddlewareInterface
{
    use MiddlewareTrait;

    use MatchRootTrait;

    /**
     * @var FilterInterface|ReleaseInterface
     */
    protected $app;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param FilterInterface|ReleaseInterface $app
     */
    public function __construct($app)
    {
        $this->app  = $app;
        $this->name = get_class($app);
    }

    /**
     * @param Request       $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        // matches requested path with the root.
        if (!$this->matchRoot($request)) {
            return $this->next ? $this->next->__invoke($request) : null;
        }
        
        // let's run the application.
        $app      = $this->app;
        $retReq   = $this->getReturnable();
        $response = $app($request, $retReq); // Application!
        $request  = $retReq->get($request);
        
        // release procedure. 
        if (is_null($response)) {
            $response = $this->execNext($request);
        }
        if ($app instanceof ReleaseInterface) {
            $response = $app->release($request, $response);
        }
        return $response;
    }

}