<?php
namespace tests\Web\stacks;

use Tuum\Web\App\AppHandleInterface;

class View implements AppHandleInterface
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
