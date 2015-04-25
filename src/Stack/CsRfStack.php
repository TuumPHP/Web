<?php
namespace Tuum\Web\Stack;

use Aura\Session\Session;
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
     * @return null|Response
     */
    public function __invoke($request)
    {
        // get session. ignore CsRf filter if not set. 
        /** @var Session $session */
        /** @var Request $request */
        $session = $request->getSession();
        if (!$session) {
            return $this->execNext($request);
        }
        /*
         * get token, and set the token value to respond 
         * so that view/response can access it.
         */
        $token   = $session->getCsrfToken();
        $request = $request->withAttribute(Web::TOKEN_NAME, $token->getValue());
        /*
         * check if token must be verified.
         */
        $reqRet = $this->getReturnable();
        if (!$matched = $this->isMatch($request, $reqRet)) {
            $request = $reqRet->get($request);
            return $this->execNext($request); // maybe not...
        }
        /*
         * validate token
         */
        /** @var CsRfFilter $csRfFilter */
        $reqRet = $this->getReturnable();
        if ($response = $this->csRfFilter->__invoke($request, $reqRet)) {
            return $response;
        }
        $request = $reqRet->get($request);
        return $this->execNext($request); // GOOD!
    }
}