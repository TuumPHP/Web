<?php
namespace Tuum\Web\Stack;

use Tuum\Stack\Http\Request;
use Tuum\Stack\Http\Response;
use Tuum\Stack\Http\View;
use Tuum\Stack\StackHandleInterface;
use Tuum\Stack\StackReleaseInterface;
use Tuum\Web\App;
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
    public function __construct($engine)
    {
        $this->engine = $engine;
    }

    /**
     * do nothing when handling the request.
     *
     * @param Request $request
     * @return Response|null
     */
    public function handle($request)
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
    public function release($request, $response)
    {
        if (!$response) {
            $response = $request->respond()->notFound();
        }
        if ($response instanceof View) {
            return $this->setContents($request, $response, $this->engine);
        }
        if (is_string($response)) {
            return $response = $request->respond()->text($response);
        }
        return $response;
    }

    /**
     * @param Request           $request
     * @param View              $response
     * @param RendererInterface $engine
     * @return Response
     */
    protected function setContents($request, $response, $engine)
    {
        $file = $response->getFile();
        $data = $response->getData();
        if ($flash = $request->attributes->get(App::FLASH_NAME)) {
            $data = array_merge($data, $flash);
        }
        $token = sha1(uniqid() . mt_rand(0, 10000));
        $request->attributes->set(App::TOKEN_NAME, $token);
        $data['_token'] = $token;

        $data['_request'] = $request;

        $content = $engine->render($file, $data);
        $response->setContent($content);
        return $response;
    }
}