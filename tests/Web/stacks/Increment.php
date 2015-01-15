<?php
namespace tests\Web\stacks;

use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;
use Tuum\Web\App\AppReleaseInterface;

class Increment implements AppReleaseInterface
{
    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    public function __invoke($request, $response)
    {
        if ($response) {
            $value = (int)$response->getContent() + 1;
            $response->setContent($value);
        }
        return $response;
    }
}