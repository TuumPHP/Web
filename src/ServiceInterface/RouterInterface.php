<?php
namespace Tuum\Web\ServiceInterface;

use Tuum\Web\Http\Request;

interface RouterInterface
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function match($request);
}