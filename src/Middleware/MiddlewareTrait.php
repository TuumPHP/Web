<?php
namespace Tuum\Web\Middleware;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

trait MiddlewareTrait
{
    /**
     * @var MiddlewareInterface
     */
    protected $next;

    /**
     * @return Returnable
     */
    protected function getReturnable()
    {
        return Returnable::start();
    }
    
    /**
     * stack up the middleware.
     * converts normal Application into middleware.
     *
     * @param ApplicationInterface $handler
     * @return $this
     */
    public function push($handler)
    {
        if(!$handler) {
            return $this;
        }
        if ($this->next) {
            return $this->next->push($handler);
        }
        if (!$handler instanceof MiddlewareInterface) {
            $handler = new Middleware($handler);
        }
        $this->next = $handler;
        return $this->next;
    }

    /**
     * prepends a new middleware/application at the
     * beginning of the stack. returns the prepended stack.
     *
     * @param ApplicationInterface $handler
     * @return MiddlewareInterface
     */
    public function prepend($handler)
    {
        if(!$handler) {
            return $this;
        }
        if (!$handler instanceof MiddlewareInterface) {
            $handler = new Middleware($handler);
        }
        $next = $this->next;
        $handler->push($next);
        $this->next = $handler;
        return $this;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    protected function execNext($request)
    {
        if(!$this->next) {
            return null;
        }
        $next = $this->next;
        return $next($request);
    }
}