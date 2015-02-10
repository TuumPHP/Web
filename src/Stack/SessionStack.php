<?php
namespace Tuum\Web\Stack;

use Aura\Session\Session;
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
        $segment = $this->session->getSegment('TuumPHP/WebApplication');
        $flash   = $segment->getFlash('flashed');
        if ($flash) {
            $request->respondWith()->with($flash);
        }
        $request = $request->withSession($this->session);

        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        /*
         * copy data from $response into session. 
         */
        if ($response->isType(Response::TYPE_REDIRECT)) {
            $data  = $response->getData();
            $segment->setFlash('flashed', $data);
        }
        if ($response->isType(Response::TYPE_VIEW)) {
            // currently, nothing to do.
        }
        $this->session->commit();
        return $response;
    }
}