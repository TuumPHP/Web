<?php
namespace Tuum\Web\Stack;

use Aura\Session\SessionFactory;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Web;

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
     * @return SessionStack
     */    
    public static function forge()
    {
        return new SessionStack(new SessionFactory);
    }

    /**
     * @param Request       $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        /*
         * first, copy session data into $request->respond. 
         */
        $session = $request->getSession();
        if (!$session) {
            return $this->execNext($request);
        }
        $segment = $session->getSegment('TuumFW');
        $flash   = $segment->getFlash('flashed');
        $flash[Web::REFERRER_URI] = $segment->get(Web::REFERRER_URI, null);
        $request = $request->withAttributes($flash);

        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        /*
         * copy data from $response into session. 
         */
        if ($response->isType(Response::TYPE_REDIRECT)) {
            $data = $response->getData();
            $segment->setFlash('flashed', $data);
        }
        elseif ($response->isType(Response::TYPE_VIEW)) {
            $segment->set(Web::REFERRER_URI, $request->getUri()->__toString());
        }
        $session->commit();
        return $response;
    }
}