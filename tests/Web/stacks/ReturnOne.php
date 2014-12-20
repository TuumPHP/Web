<?php
namespace tests\Web\stacks;

use Tuum\Web\Stack\StackHandleInterface;

class ReturnOne implements StackHandleInterface
{
    public function handle( $request )
    {
        return $request->respond()->text(1);
    }

}
