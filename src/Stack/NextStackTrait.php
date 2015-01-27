<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppMarkerInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

trait NextStackTrait
{
    /**
     * pile of Stackable Http Kernels.
     *
     * @var StackableInterface
     */
    protected $next;


    /**
     * stack up the SplStack.
     * converts normal HttpKernel into Stackable.
     *
     * @param AppMarkerInterface|StackableInterface $handler
     * @return $this
     */
    public function push($handler)
    {
        if ($this->next) {
            return $this->next->push($handler);
        }
        $this->next = Stackable::makeStack($handler);
        return $this->next;
    }

    /**
     * @param Request  $request
     * @return null|Response
     */
    protected function execNext($request)
    {
        // execute the next handler.
        if ($this->next) {
            return $this->next->__invoke($request);
        }
        return null;
    }

}