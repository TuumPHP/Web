<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Viewer\View;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\View\ViewEngineInterface;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\StreamFactory;

class ViewStack implements MiddlewareInterface
{
    use MiddlewareTrait;

    /**
     * @var ViewEngineInterface
     */
    public $engine;

    /**
     * @param ViewEngineInterface $engine
     */
    public function __construct($engine)
    {
        $this->engine = $engine;
    }

    /**
     * render view file if the $response is a View object.
     *
     * @param Request  $request
     * @return Response|null
     */
    public function __invoke($request)
    {
        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        if (!$response) {
            return $request->respond()->asNotFound();
        }
        if ($response->isType(Response::TYPE_VIEW)) {
            return $this->setContents($request, $response);
        }
        if (is_string($response)) {
            return $request->respond()->asText($response);
        }
        if (is_array($response)) {
            return $request->respond()->asJson($response);
        }
        return $response;
    }

    /**
     * @param Request           $request
     * @param Response          $response
     * @return Response
     */
    protected function setContents($request, $response)
    {
        // render view file.
        $data = $this->prepareData($request, $response);
        $file = $response->getViewFile();
        $content = $this->engine->render($file, $data);
        $response = $response->withBody(StreamFactory::string($content));
        return $response;
    }

    /**
     * @param Request           $request
     * @param Response          $response
     * @return mixed
     */
    protected function prepareData($request, $response)
    {
        $data = $response->getData();
        $view = new View($data);
        $view->setUri($request->getUri());
        $data = ['view' => $view];
        return $data;
    }
}