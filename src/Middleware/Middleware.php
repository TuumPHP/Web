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
     * @param ApplicationInterface $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function __invoke($request)
    {
        if ($this->isMatch($request)) {
            $app      = $this->app;
            $response = $app($request);
            if ($response) {
                return $response;
            }
        }
        $next = $this->next;
        if ($next) {
            return $next($request);
        }
        return null;
    }

}