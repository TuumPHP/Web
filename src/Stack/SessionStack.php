<?php
namespace Tuum\Web\Stack;

use Symfony\Component\HttpFoundation\Session\Session;
use Tuum\Web\App;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;

class SessionStack implements MiddlewareInterface
{
    use MiddlewareTrait;

    const FLASH_NAME = 'flashed';

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * @param Request $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        /*
         * first, copy session data into $request->respond. 
         */
        $flash = $this->session->getFlashBag()->all();
        $token = $this->session->get(App::TOKEN_NAME);
        if ($flash || $token) {
            if (!$flash) {
                $flash = [];
            }
            if ($token) {
                $flash[App::TOKEN_NAME] = $token;
            }
            $request->respondWith($flash);
        }
        $request = $request->withAttribute( App::SESSION_MGR, $this->session);

        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        /*
         * copy data from $response into session. 
         */
        if ($response->isType(Response::TYPE_REDIRECT)) {
            $data  = $response->getData();
            $this->session->getFlashBag()->setAll($data);
        }
        if ($response->isType(Response::TYPE_VIEW)) {
            $token = $response->getData(App::TOKEN_NAME);
            $this->session->set(App::TOKEN_NAME, $token);
        }
        $this->session->save();
        return $response;
    }
}