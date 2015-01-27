<?php
namespace tests\Web\stacks;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\Stack\NextStackTrait;
use Tuum\Web\Stack\StackableInterface;

class Increment implements StackableInterface
{
    use NextStackTrait;

    /**
     * @param Request  $request
     * @return Response
     */
    public function execute($request)
    {
        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        if ($response) {
            $value = (int)$response->getContent() + 1;
            $response->setContent($value);
        }
        return $response;
    }
}