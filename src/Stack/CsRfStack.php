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
class CsRfStack  implements MiddlewareInterface
{
    use MiddlewareTrait;

    use MatchRootTrait;

    /**
     * @param Request $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        // get session. ignore CsRf filter if not set. 
        /** @var Session $session */
        $session = $request->getSession();
        if(!$session) {
            return $this->execNext($request);
        }
        /*
         * get token, and set the token value to respond 
         * so that view/response can access it.
         */
        $token = $session->getCsrfToken(Web::TOKEN_NAME);
        $request->respondWith()->with(Web::TOKEN_NAME, $token->getValue());
        /*
         * check if token must be verified.
         */
        if(!$matched = $this->isMatch($request)) {
            return $this->execNext($request); // maybe not...
        }
        if(isset($matched['matched'])) {
            $request = $request->withPathToMatch($matched['matched'], $matched['trailing']);
        }
        /*
         * validate token
         */
        /** @var CsRfFilter $csRfFilter */
        $csRfFilter = $request->getFilter(Web::CS_RF_FILTER);
        if( $response = $csRfFilter($request)) {
            return $response;
        }
        return $this->execNext($request); // GOOD!
    }
}