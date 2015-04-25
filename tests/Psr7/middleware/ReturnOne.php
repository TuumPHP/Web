<?php
namespace tests\Psr7\middleware;

use Tuum\Web\ApplicationInterface;

class ReturnOne implements ApplicationInterface
{
    public function __invoke($request)
    {
        return $request->respond()->asText('1');
    }

}
