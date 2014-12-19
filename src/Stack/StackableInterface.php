<?php
namespace Tuum\Web\Stack;

interface StackableInterface extends StackHandleInterface
{
    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param StackHandleInterface $handler
     * @return $this
     */
    public function push(StackHandleInterface $handler);
}