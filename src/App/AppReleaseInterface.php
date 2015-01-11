<?php
namespace Tuum\Web\App;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface AppReleaseInterface
{
    /**
     * @param Request  $request
     * @param Response $response
     * @return Response|null
     */
    public function release($request, $response);
}