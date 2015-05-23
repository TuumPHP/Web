<?php
namespace Tuum\Web\Stack;

use Aura\Session\Segment;
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
     * @param null|Response $response
     * @param null|\Closure  $next
     * @return null|Response
     */
    public function __invoke($request, $response = null, $next = null)
    {
        // get session or throw a RuntimeException.
        $session = $request->getSession();
        if (!$session) {
            throw new \RuntimeException('Missing session manager');
        }
        $segment = $session->getSegment('TuumFW');
        $request = $this->prepare($request, $segment);

        /*
         * execute the subsequent stack.
         */
        $response = $this->next ? $this->next->__invoke($request) : null;

        // release process.
        $this->release($request, $response, $segment);
        $session->commit();
        return $response;
    }

    /**
     * copy session data into $request's attribute.
     *
     * @param Request $request
     * @param Segment $segment
     * @return Request
     */
    private function prepare($request, $segment)
    {
        $flash = [Web::REFERRER_URI => $segment->get(Web::REFERRER_URI, null)];
        $flash += (array)$segment->getFlash('flash-info') ?: [];
        $flash += (array)$segment->getFlash('flash-data') ?: [];
        return $request->withAttributes($flash);
    }

    /**
     * copy data from $response into session.
     *
     * @param Request  $request
     * @param Response $response
     * @param Segment  $segment
     */
    private function release($request, $response, $segment)
    {
        if ($data = $response->getFlashData()) {
            // must get flash data first (before redirect's getData)
            // to clear the flash data. Otherwise, the flash-data
            // will reappear in the sub-sequent request.
            $segment->setFlash('flash-data', $data);
        }
        if ($response->isType(Response::TYPE_REDIRECT)) {
            $data = $response->getData();
            $segment->setFlash('flash-info', $data);
        } elseif ($response->isType(Response::TYPE_VIEW)) {
            $segment->set(Web::REFERRER_URI, $request->getUri()->__toString());
        }
    }
}