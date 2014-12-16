<?php
namespace Tuum\Web;

use Tuum\Stack\Http\Request;
use Tuum\Stack\Http\Response;
use Tuum\Web\Stack\Stack;
use Tuum\Stack\StackableInterface;
use Tuum\Stack\StackHandleInterface;
use Tuum\Locator\Container;

class App
{
    const TOKEN_NAME  = 'token';
    const FLASH_NAME  = 'flash';
    const ROUTE_PARAM = 'params';
    const ROUTE_NAMES = 'namedRoutes';

    /**
     * @var Container
     */
    public $container;

    /**
     * @var StackableInterface
     */
    public $stack;

    /**
     * @param Container $container
     */
    public function __construct( $container )
    {
        $this->container = $container;
    }

    /**
     * @param string $key
     * @param array  $data
     * @return mixed
     */
    public function get( $key, $data=[] )
    {
        return $this->container->get( $key, $data );
    }

    // +----------------------------------------------------------------------+
    //  managing instance and stacks
    // +----------------------------------------------------------------------+
    /**
     * @param StackHandleInterface $stack
     * @return StackableInterface
     */
    public function push( $stack )
    {
        if ( $this->stack ) {
            return $this->stack->push( $stack );
        }
        $this->stack = Stack::makeStack( $stack );
        return $this->stack;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle( $request )
    {
        return $this->stack->handle( $request );
    }

}