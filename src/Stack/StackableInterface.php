<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface StackableInterface 
{
    /**
     * @param Request $request
     * @return Response|null
     */
    public function execute($request);

    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param AppHandleInterface $handler
     * @return $this
     */
    public function push(AppHandleInterface $handler);
}