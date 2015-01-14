<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface StackableInterface 
{
    /**
     * @param Request  $request
     * @param Response $response
     * @return null|Response
     */
    public function execute($request, $response);

    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param AppHandleInterface $handler
     * @return $this
     */
    public function push(AppHandleInterface $handler);
}