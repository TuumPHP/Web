<?php
namespace Tuum\Web\App;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface AppHandleInterface
{
    /**
     * @param Request $request
     * @return Response|null
     */
    public function handle($request);
}