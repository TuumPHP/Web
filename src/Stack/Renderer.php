<?php
namespace Tuum\Web\Stack;

use Tuum\Stack\Http\Request;
use Tuum\Stack\Http\Response;
use Tuum\Stack\Http\View;
use Tuum\Stack\StackHandleInterface;
use Tuum\Stack\StackReleaseInterface;
use Tuum\Web\View\RendererInterface;

class Renderer implements StackHandleInterface, StackReleaseInterface
{
    /**
     * @var RendererInterface
     */
    public $engine;

    /**
     * @param RendererInterface $engine
     */
    public function __construct( $engine )
    {
        $this->engine = $engine;
    }

    /**
     * do nothing when handling the request.
     *
     * @param Request $request
     * @return Response|null
     */
    public function handle( $request )
    {
        return null;
    }

    /**
     * render view file if the $response is a View object.
     *
     * @param Request  $request
     * @param Response $response
     * @return Response|null
     */
    public function release( $request, $response )
    {
        if ( !$response ) {
            $response = $request->respond()->notFound();
        }
        if ( $response instanceof View ) {
            return $this->setContents( $request, $response, $this->engine );
        }
        if ( is_string( $response ) ) {
            return $response = $request->respond()->text( $response );
        }
        return $response;
    }

    /**
     * @param Request           $request
     * @param View              $response
     * @param RendererInterface $engine
     * @return Response
     */
    protected function setContents( $request, $response, $engine )
    {
        $file = $response->getFile();
        $data = $response->getData();
        if ( $flash = $request->attributes->get( 'flash' ) ) {
            $data = array_merge( $data, $flash );
        }
        $data[ '_request' ] = $request;

        $content = $engine->render( $file, $data );
        $response->setContent( $content );
        return $response;
    }
}