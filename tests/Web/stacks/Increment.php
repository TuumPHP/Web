<?php
namespace tests\App\Stackable;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use WScore\Pile\Stack\ReleaseInterface;

class Increment implements HttpKernelInterface, ReleaseInterface
{
    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int     $type The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle( Request $request, $type = self::MASTER_REQUEST, $catch = true )
    {
    }

    /**
     * @param Response $response
     * @return Response
     */
    public function release( $response )
    {
        if( $response ) {
            $value = (int) $response->getContent() + 1;
            $response->setContent( $value );
        }
        return $response;
    }
}