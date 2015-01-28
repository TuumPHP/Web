<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppMarkerInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface StackableInterface extends AppMarkerInterface
{
    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param AppMarkerInterface|StackableInterface $handler
     * @return $this
     */
    public function push($handler);
}