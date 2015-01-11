<?php
namespace tests\Web\stacks;

use Tuum\Web\App\AppHandleInterface;

class ReturnOne implements AppHandleInterface
{
    public function handle( $request )
    {
        return $request->respond()->text(1);
    }

}
