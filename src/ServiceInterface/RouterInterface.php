<?php
namespace Tuum\Web\ServiceInterface;

use Tuum\Web\Http\Request;

/**
 * Interface RouterInterface
 * 
 * an interface for matching a route against a request. 
 *
 * @package Tuum\Web\ServiceInterface
 */
interface RouterInterface
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function match($request);
}