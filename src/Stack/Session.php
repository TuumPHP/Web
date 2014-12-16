<?php
namespace Tuum\Web\Stack;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Tuum\Stack\Http\Redirect;
use Tuum\Stack\Http\Request;
use Tuum\Stack\Http\Response;
use Tuum\Stack\Http\View;
use Tuum\Stack\StackHandleInterface;
use Tuum\Stack\StackReleaseInterface;

class Session implements StackHandleInterface, StackReleaseInterface
{

    /**
     * set token for CsRf and flashed data from session into attributes.
     *
     * @param Request $request
     * @return Response|null
     */
    public function handle( $request )
    {
        /** @var SymfonySession $session */
        $session = $request->getSession();
        $flash   = $session->getFlashBag();
        if ( $flash ) {
            $request->attributes->set( 'flash', $flash->get( 'flash' ) );
        }
        if( $token = $session->get( 'token' ) ) {
            $request->attributes->set( 'token', $token );
        }
        return null;
    }

    /**
     * saves
     * - token of View response into session, or
     * - data of Redirect response into flash.
     *
     * @param Request  $request
     * @param Response $response
     * @return Response|null
     */
    public function release( $request, $response )
    {
        if ( $response instanceof Redirect )
        {
            /** @var SymfonySession $session */
            $session = $request->getSession();
            $flash   = $session->getFlashBag();
            $data    = $response->getData();
            $flash->set( 'flash', $data );
        }
        if ( $response instanceof View )
        {
            $data  = $response->getData();
            $token = $data[ '_token' ];
            /** @var SymfonySession $session */
            $session = $request->getSession();
            $session->set( 'token', $token );
        }
        if ( isset( $session ) ) {
            $session->save();
        }
        return $response;
    }
}
