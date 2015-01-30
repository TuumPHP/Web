<?php
namespace Tuum\Web\Middleware;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\MiddlewareInterface;

trait MiddlewareTrait
{
    /**
     * @var MiddlewareInterface
     */
    protected $next;

    /**
     * stack up the middleware.
     * converts normal Application into middleware.
     *
     * @param ApplicationInterface $handler
     * @return $this
     */
    public function push(ApplicationInterface $handler)
    {
        if ($this->next) {
            return $this->next->push($handler);
        }
        if (!$handler instanceof MiddlewareInterface) {
            $handler = new Middleware($handler);
        }
        $this->next = $handler;
        return $this->next;
    }
}