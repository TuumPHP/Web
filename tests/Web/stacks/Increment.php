<?php
namespace tests\Web\stacks;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\App\AppReleaseInterface;

class Increment implements AppHandleInterface, AppReleaseInterface
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