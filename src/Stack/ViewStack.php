<?php
namespace Tuum\Web\Stack;

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
     * @param Request       $request
     * @param callable|null $next
     * @return null|Response
     */
    public function __invoke($request, $next = null)
    {
        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        if (!$response) {
            return $request->respond()->asNotFound();
        }
        if (is_string($response)) {
            return $request->respond()->asText($response);
        }
        if (is_array($response)) {
            return $request->respond()->asJson($response);
        }
        if ($response instanceof Response &&
            $response->isType(Response::TYPE_VIEW)
        ) {
            return $this->setContents($request, $response);
        }
        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    protected function setContents($request, $response)
    {
        // render view file.
        $data        = $response->getData();
        $data['uri'] = $request->getUri();
        $file        = $response->getViewFile();
        $content     = $this->engine->render($file, $data);
        $response    = $response->withBody(StreamFactory::string($content));
        return $response;
    }
}