<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Web;
use Tuum\Web\Filter\CsRfFilter;
use Tuum\Web\Middleware\MatchRootTrait;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

/**
 * Class CsRfStack
 *
 * Cross Site Resource Forgery token.
 * run SessionStack middleware before this stack.
 *
 * @package Tuum\Web\Stack
 */
class CsRfStack implements MiddlewareInterface
{
    use MiddlewareTrait;

    use MatchRootTrait;

    /**
     * @var CsRfFilter
     */
    private $csRfFilter;

    /**
     * @param CsRfFilter $filter
     */
    public function __construct($filter)
    {
        $this->csRfFilter = $filter;
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
        /*
         * get token, and set the token value to respond 
         * so that view/response can access it.
         */
        $token   = $session->getCsrfToken();
        $request = $request->withAttribute(Web::TOKEN_NAME, $token->getValue());

        // matches requested path with the root.
        if (!$this->matchRoot($request)) {
            return $this->next ? $this->next->__invoke($request) : null;
        }
        return $this->verifyToken($request);
    }

    /**
     * @param $request
     * @return null|Response
     */
    private function verifyToken($request)
    {
        /*
         * validate token
         */
        /** @var CsRfFilter $csRfFilter */
        $reqRet = $this->getReturnable();
        if ($response = $this->csRfFilter->__invoke($request, $reqRet)) {
            return $response;
        }
        $request = $reqRet->get($request);
        return $this->next ? $this->next->__invoke($request) : null;
    }
}