<?php
namespace tests\Web\stacks;

use Tuum\Web\Stack\StackHandleInterface;

class View implements StackHandleInterface
{
    public function handle( $request )
    {
        return $request->respond()->view('tested-view')
            ->withErrorMsg( 'tested')
            ->withValidationMsg(['more'=>'done'])
            ->fill(['test'=>'tested'])
            ;
    }

}
