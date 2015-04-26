<?php
namespace Tuum\Web\Stack;

use Psr\Log\LoggerInterface;
use Tuum\Web\View\ErrorView;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\View\ViewEngineInterface;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\StreamFactory;
use Tuum\Web\View\ViewStream;

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
     * @return null|Response
     */
    public function __invoke($request)
    {
        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        if (is_null($response)) {
            // no response. turn it to not-found response.
            $response = $request->respond()->asNotFound();
        }
        if (is_string($response)) {
            // return as a plain text.
            return $request->respond()->asText($response);
        }
        if (is_array($response)) {
            // return as a JSON.
            return $request->respond()->asJson($response);
        }
        if (!$response instanceof Response) {
            // what is this? just return it.
            return $response;
        }
        /*
         * fill up contents for VIEW and ERROR responses.
         */
        if ($response->isType(Response::TYPE_ERROR) && 
            !$response->getBody() instanceof ViewStream) {
            return $this->setErrorView($response);
        }
        return $response;
    }

    /**
     * render error pages.
     *
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
}