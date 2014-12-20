<?php
namespace Tuum\Web\Http;

use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectNamed extends RedirectResponse
{
    use ResponseWithTrait;

    public $namedRoute;
    public $namedParam = [];

    /**
     * @param int   $status
     * @param array $headers
     */
    public function __construct($name, $args, $status = 302, $headers = array())
    {
        parent::__construct('', $status, $headers);
        $this->namedRoute = $name;
        $this->namedParam = $args;

        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }
    }
}