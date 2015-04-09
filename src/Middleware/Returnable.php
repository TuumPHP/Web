<?php
namespace Tuum\Web\Middleware;

use Tuum\Web\Psr7\Request;

class Returnable
{
    /**
     * @var mixed
     */
    private $return = null;

    /**
     * @var Returnable
     */
    private static $self;

    /**
     * private constructor. can only use static start().
     */
    private function __construct()
    {
    }

    /**
     * @return Returnable
     */
    public static function start()
    {
        if (!self::$self) {
            self::$self = new Returnable();
        }

        return clone(self::$self);
    }

    /**
     * @param Request $return
     */
    public function __invoke($return)
    {
        $this->return = $return;
    }

    /**
     * @param Request $request
     * @return Request
     */
    public function get($request)
    {
        if (is_null($this->return)) {
            return $request;
        }
        if ($this->return instanceof Request) {
            return $this->return;
        }
        if (is_array($this->return)) {
            return $request->withAttributes($this->return);
        }

        return $request;
    }
}