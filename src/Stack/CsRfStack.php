<?php
namespace Tuum\Web\Stack;

use Aura\Session\Session;
use Tuum\Web\App;
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
        /** @var Session $session */
        $session = $request->getAttribute(App::SESSION_MGR);
        if(!$session) {
            return $this->execNext($request);
        }
        $token = $session->getCsrfToken(App::TOKEN_NAME);
        $request->respondWith(App::TOKEN_NAME, $token->getValue());
        /*
         * check if token must be verified.
         */
        if(!$this->isMatch($request)) {
            return $this->execNext($request); // maybe not...
        }
        /*
         * check for token in post data.
         */
        $posts = $request->getBodyParams();
        if( isset($posts[App::TOKEN_NAME]) &&
            $posts[App::TOKEN_NAME] &&
            $token->isValid($posts[App::TOKEN_NAME])) {
            return $this->execNext($request); // GOOD!
        }
        return $request->respond()->asForbidden(); // BAD!!!
    }
}