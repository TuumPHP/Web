<?php
namespace tests\Web\stacks;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\App\AbstractApp;

class MatchRoute extends AbstractApp
{
    /**
     * @param Request $request
     * @return Response|null
     */
    public function handle($request)
    {
        return $request->respond()->text($request->getPathInfo());

    }
}