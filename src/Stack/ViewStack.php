<?php
namespace Tuum\Web\Stack;

use Psr\Log\LoggerInterface;
use Tuum\View\ErrorView;
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
    private $engine;

    /**
     * @var ErrorView
     */
    private $error;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * @param ViewEngineInterface  $engine
     * @param ErrorView            $errorView
     * @param null|LoggerInterface $logger
     */
    public function __construct($engine, $errorView, $logger)
    {
        $this->engine = $engine;
        $this->error  = $errorView;
        $this->logger = $logger;
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

        /*
         * if no response, convert to not-found response.
         */
        if (!$response) {
            $response = $request->respond()->asNotFound();
        }
        // in case $response is a text or an array.
        if (is_string($response)) {
            return $request->respond()->asText($response);
        }
        if (is_array($response)) {
            return $request->respond()->asJson($response);
        }
        // what's this? just return it.
        if (!$response instanceof Response) {
            return $response;
        }
        /*
         * fill up contents for VIEW and ERROR responses.
         */
        if ($response->isType(Response::TYPE_VIEW)) {
            return $this->setContents($request, $response);
        }
        if ($response->isType(Response::TYPE_ERROR)) {
            return $this->setErrorView($response);
        }
        return $response;
    }

    /**
     * @param Response $response
     * @return Response
     */
    protected function setErrorView($response)
    {
        if ($this->logger) {
            $this->logger->error('ErrorRelease: received an error response: ' . $response->getStatusCode());
        }
        $content  = $this->error->render($response->getStatusCode(), $response->getData());
        $response = $response->withBody(StreamFactory::string($content));
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