<?php
namespace Tuum\Web;

/**
 * Interface MiddlewareInterface
 *
 * a http job middleware interface.
 * make sure to call the next handle in the __invoke, if exist.
 *
 * @package Tuum\Web
 */
interface MiddlewareInterface extends ApplicationInterface
{
    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param ApplicationInterface $handler
     * @return $this
     */
    public function push($handler);

    /**
     * prepends a new middleware/application at the
     * beginning of the stack. returns the prepended stack.
     *
     * @param ApplicationInterface $handler
     * @return MiddlewareInterface
     */
    public function prepend($handler);
}