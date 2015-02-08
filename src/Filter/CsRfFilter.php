<?php
namespace Tuum\Web\Filter;

use Aura\Session\Session;
use Tuum\Web\App;
use Tuum\Web\ApplicationInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class CsRfFilter implements ApplicationInterface
{

    /**
     * @param Request $request
     * @return null|Response
     */
    public function __invoke($request)
    {
        /*
         * get token from session. ignore CsRf if session is not set.
         */
        /** @var Session $session */
        $session = $request->getSession();
        if(!$session) {
            return null;
        }
        $token = $session->getCsrfToken(App::TOKEN_NAME);
        /*
         * check for token in post data.
         */
        $posts = $request->getBodyParams();
        if( isset($posts[App::TOKEN_NAME]) &&
            $posts[App::TOKEN_NAME] &&
            $token->isValid($posts[App::TOKEN_NAME])) {
            return null; // GOOD!
        }
        return $request->respond()->asForbidden(); // BAD!!!
    }
}