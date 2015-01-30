<?php
namespace tests\Psr7\middleware;

use Phly\Http\Stream;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\Psr7\StreamFactory;

class Increment implements MiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @param Request  $request
     * @return Response
     */
    public function __invoke($request)
    {
        /*
         * execute the subsequent stack.
         */
        $response = null;
        if($this->next) {
            $response = $this->next->__invoke($request);
        }

        if ($response) {
            $value = 1 + (int) ((string)$response->getBody());
            $response = $response->withBody(StreamFactory::string($value));
        }
        return $response;
    }
}