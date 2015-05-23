<?php
namespace Tuum\Web\Middleware;

use Tuum\Web\ApplicationInterface;
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
     * @param ApplicationInterface $app
     */
    public function __construct($app)
    {
        $this->app  = $app;
        $this->name = get_class($app);
    }

    /**
     * @param Request       $request
     * @param null|Response $response
     * @param null|\Closure  $next
     * @return null|Response
     */
    public function __invoke($request, $response = null, $next = null)
    {
        // matches requested path with the root.
        if (!$this->matchRoot($request)) {
            return $this->next ? $this->next->__invoke($request) : null;
        }
        
        // let's run the filter application.
        if (!$this->app instanceof ReleaseInterface) {
            $retReq   = $this->getReturnable();
            $response = $this->app->__invoke($request, $retReq); // Application!
            $request  = $retReq->get($request);
        }
        
        // invoke next middleware. 
        if (is_null($response)) {
            $response = $this->next ? $this->next->__invoke($request) : null;
        }
        
        // release process.
        if ($this->app instanceof ReleaseInterface) {
            $response = $this->app->__invoke($request, $response);
        }
        return $response;
    }

}