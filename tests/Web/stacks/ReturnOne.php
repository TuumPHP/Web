<?php
namespace tests\Web\stacks;

use Tuum\Web\App\AppHandleInterface;

class ReturnOne implements AppHandleInterface
{
    public function __invoke( $request )
    {
        return $request->respond()->text(1);
    }

}
