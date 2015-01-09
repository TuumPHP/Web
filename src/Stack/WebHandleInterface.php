<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface WebHandleInterface
{
    /**
     * @param Request $request
     * @return Response|null
     */
    public function handle($request);
}