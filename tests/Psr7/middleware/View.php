<?php
namespace tests\Psr7\middleware;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class View implements ApplicationInterface
{
    /**
     * @param Request          $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        return $request->respond()
            ->withError( 'tested')
            ->withInput(['more'=>'done'])
            ->with(['test'=>'tested'])
            ->asView('tested-view')
            ;
    }

}
