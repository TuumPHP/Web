<?php
namespace tests\Web\stacks;

use Tuum\Web\App\AppHandleInterface;

class Location implements AppHandleInterface
{
    public function __invoke( $request )
    {
        return $request->redirect()->location('tested-location.php')
            ->with( 'test', 'tested')
            ->withMessage('message-test')
            ->withInput(['more'=>'done'])
            ;
    }

}
