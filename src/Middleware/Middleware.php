<?php
namespace Tuum\Web\Middleware;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

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

    use BeforeFilterTrait;

    /**
     * @var ApplicationInterface
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
     * @return null|Response
     */
    public function __invoke($request)
    {
        $retReq = $this->getReturnable();
        if ($matched = $this->isMatch($request, $retReq)) {
            $request  = $retReq->get($request);
            $app      = $this->app;
            $retReq   = $this->getReturnable();
            $response = $app($request, $retReq);
            if ($response) {
                return $response;
            }
            $request = $retReq->get($request);
        }
        $next = $this->next;
        if ($next) {
            return $next($request);
        }
        return null;
    }

}