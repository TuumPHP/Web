<?php
namespace tests\Web\stacks;

use Tuum\Web\Stack\WebHandleInterface;

class Location implements WebHandleInterface
{
    public function handle( $request )
    {
        return $request->redirect()->location('tested-location.php')
            ->with( 'test', 'tested')
            ->withMessage('message-test')
            ->withInput(['more'=>'done'])
            ;
    }

}
