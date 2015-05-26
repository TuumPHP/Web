<?php
namespace Tuum\Web;

use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

/**
 * Interface ReleaseInterface
 *
 * a release interface applied after the request is handled. 
 *
 * @package Tuum\Web
 */
interface ReleaseInterface
{
    /**
     * @param Request  $request
     * @param null|Response $response
     * @return null|Response
     */
    public function release($request, $response);
}