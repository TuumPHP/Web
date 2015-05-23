<?php
namespace tests\Psr7\middleware;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class ReturnOne implements ApplicationInterface
{
    /**
     * @param Request       $request
     * @param null|Response $response
     * @param null|\Closure  $next
     * @return null|Response
     */
    public function __invoke($request, $response = null, $next = null)
    {
        return $request->respond()->asText('1');
    }

}
