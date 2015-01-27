<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppMarkerInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface StackableInterface 
{
    /**
     * @param Request  $request
     * @return null|Response
     */
    public function execute($request);

    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param AppMarkerInterface|StackableInterface $handler
     * @return $this
     */
    public function push($handler);
}