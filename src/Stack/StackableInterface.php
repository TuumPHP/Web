<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppHandleInterface;

interface StackableInterface extends AppHandleInterface
{
    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param AppHandleInterface $handler
     * @return $this
     */
    public function push(AppHandleInterface $handler);
}