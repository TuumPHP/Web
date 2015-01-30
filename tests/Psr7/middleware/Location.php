<?php
namespace tests\Psr7\middleware;

use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class Location implements ApplicationInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function __invoke( $request )
    {
        return $request->respond()
            ->with( 'test', 'tested')
            ->withMessage('message-test')
            ->withInput(['more'=>'done'])
            ->asPath('tested-location.php')
            ;
    }

}
