<?php
namespace Tuum\Web\App;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

interface AppReleaseInterface extends AppMarkerInterface
{
    /**
     * @param Request  $request
     * @param Response $response
     * @return Response|null
     */
    public function __invoke($request, $response);
}