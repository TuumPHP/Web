<?php
namespace Tuum\Web\Stack;

use Symfony\Component\HttpFoundation\Session\Session;
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
        /*
         * set up CsRf token if not set.
         * use the same token for the duration of the session.
         */
        if(!$session->get(App::TOKEN_NAME)) {
            $session->set(App::TOKEN_NAME, $this->calToken());
        }
        /*
         * check if token must be verified.
         */
        if(!$this->isMatch($request)) {
            return $this->execNext($request); // maybe not...
        }
        /*
         * check for token in post data.
         */
        $token = $session->get(App::TOKEN_NAME);
        $posts = $request->getBodyParams();
        if( isset($posts[App::TOKEN_NAME]) &&
            $posts[App::TOKEN_NAME] &&
            $posts[App::TOKEN_NAME] === $token) {
            return $this->execNext($request); // GOOD!
        }
        return $request->respond()->asForbidden(); // BAD!!!
    }

    /**
     * TODO: update the logic with more secure one!
     *
     * @return string
     */
    protected function calToken()
    {
        return sha1(uniqid() . mt_rand(0, 10000));
    }
}