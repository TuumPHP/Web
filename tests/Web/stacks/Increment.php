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
     * @param Response $response
     * @return Response
     */
    public function execute($request, $response)
    {
        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request, $response);

        if ($response) {
            $value = (int)$response->getContent() + 1;
            $response->setContent($value);
        }
        return $response;
    }
}