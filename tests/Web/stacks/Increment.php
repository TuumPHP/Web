<?php
namespace tests\Web\stacks;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\Stack\StackHandleInterface;
use Tuum\Web\Stack\StackReleaseInterface;

class Increment implements StackHandleInterface, StackReleaseInterface
{
    /**
     * @param Request $request
     * @return null|Response|void
     */
    public function handle($request)
    {
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    public function release($request, $response)
    {
        if ($response) {
            $value = (int)$response->getContent() + 1;
            $response->setContent($value);
        }
        return $response;
    }
}