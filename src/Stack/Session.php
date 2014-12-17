<?php
namespace Tuum\Web\Stack;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Tuum\Stack\Http\Redirect;
use Tuum\Web\App;
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
    public function handle($request)
    {
        /** @var SymfonySession $session */
        $session = $request->getSession();
        $flash   = $session->getFlashBag();
        if ($flash) {
            $request->attributes->set(App::FLASH_NAME, $flash->get(App::FLASH_NAME));
        }
        if ($token = $session->get(App::TOKEN_NAME)) {
            $request->attributes->set(App::TOKEN_NAME, $token);
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
    public function release($request, $response)
    {
        if ($response instanceof Redirect) {
            /** @var SymfonySession $session */
            $session = $request->getSession();
            $flash   = $session->getFlashBag();
            $data    = $response->getData();
            $flash->set(App::FLASH_NAME, $data);
        }
        if ($response instanceof View) {
            $data  = $response->getData();
            $token = $data[App::TOKEN_NAME];
            /** @var SymfonySession $session */
            $session = $request->getSession();
            $session->set(App::TOKEN_NAME, $token);
        }
        if (isset($session)) {
            $session->save();
        }
        return $response;
    }
}
