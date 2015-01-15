<?php
namespace Tuum\Web\App;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface AppHandleInterface extends AppMarkerInterface
{
    /**
     * @param Request $request
     * @return Response|null
     */
    public function __invoke($request);
}