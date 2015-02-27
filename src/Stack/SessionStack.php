<?php
namespace Tuum\Web\Stack;

use Aura\Session\Session;
use Aura\Session\SessionFactory;
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
     * @var SessionFactory
     */
    private $factory;

    /**
     * @param SessionFactory $factory
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
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
        $session = $this->factory->newInstance($_COOKIE);
        $segment = $session->getSegment('TuumPHP/WebApplication');
        $flash   = $segment->getFlash('flashed');
        if ($flash) {
            $request->respondWith()->with($flash);
        }
        $request = $request->withSession($session);

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
        $session->commit();
        return $response;
    }
}