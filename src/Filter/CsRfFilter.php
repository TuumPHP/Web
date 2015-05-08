<?php
namespace Tuum\Web\Filter;

use Aura\Session\Session;
use Tuum\Web\FilterInterface;
use Tuum\Web\Web;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class CsRfFilter implements FilterInterface
{
    public function __construct()
    {
    }

    /**
     * @param Request       $request
     * @param callable|null $next
     * @return null|Response
     */
    public function __invoke($request, $next = null)
    {
        /*
         * get token from session. ignore CsRf if session is not set.
         */
        /** @var Session $session */
        $session = $request->getSession();
        if (!$session) {
            return null;
        }
        $token = $session->getCsrfToken();
        /*
         * check for token in post data.
         */
        $posts = $request->getParsedBody();
        if (isset($posts[Web::TOKEN_NAME]) &&
            $posts[Web::TOKEN_NAME] &&
            $token->isValid($posts[Web::TOKEN_NAME])
        ) {
            return null; // GOOD!
        }
        return $request->respond()->asForbidden(); // BAD!!!
    }
}