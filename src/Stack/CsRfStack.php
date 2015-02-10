<?php
namespace Tuum\Web\Stack;

use Aura\Session\Session;
use Tuum\Web\App;
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
        $token = $session->getCsrfToken(App::TOKEN_NAME);
        $request->respondWith()->with(App::TOKEN_NAME, $token->getValue());
        /*
         * check if token must be verified.
         */
        if(!$this->isMatch($request)) {
            return $this->execNext($request); // maybe not...
        }
        /*
         * validate token
         */
        /** @var CsRfFilter $csRfFilter */
        $csRfFilter = $request->getFilter(App::CS_RF_FILTER);
        if( $response = $csRfFilter($request)) {
            return $response;
        }
        return $this->execNext($request); // GOOD!
    }
}