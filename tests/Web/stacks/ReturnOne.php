<?php
namespace tests\Web\stacks;

use Tuum\Web\Stack\WebHandleInterface;

class ReturnOne implements WebHandleInterface
{
    public function handle( $request )
    {
        return $request->respond()->text(1);
    }

}
