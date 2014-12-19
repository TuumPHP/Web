<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface StackReleaseInterface
{
    /**
     * @param Request  $request
     * @param Response $response
     * @return Response|null
     */
    public function release($request, $response);
}