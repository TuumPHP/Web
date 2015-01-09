<?php
namespace Tuum\Web\Stack;

interface StackableInterface extends WebHandleInterface
{
    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param WebHandleInterface $handler
     * @return $this
     */
    public function push(WebHandleInterface $handler);
}