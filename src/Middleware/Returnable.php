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
        if (!static::$self) {
            static::$self = new Returnable();
        }

        return clone(static::$self);
    }

    /**
     * @param mixed $return
     */
    public function __invoke($return)
    {
        $this->return = $return;
    }

    /**
     * @param Request $request
     * @param string  $name
     * @return Request
     */
    public function get($request, $name)
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

        return $request->withAttribute($name, $this->return);
    }
}