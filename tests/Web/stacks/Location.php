<?php
namespace tests\Web\stacks;

use Tuum\Web\Stack\StackHandleInterface;

class Location implements StackHandleInterface
{
    public function handle( $request )
    {
        return $request->respond()->redirect('tested-location.php')
            ->with( 'test', 'tested')
            ->withMessage('message-test')
            ->withInput(['more'=>'done'])
            ;
    }

}
