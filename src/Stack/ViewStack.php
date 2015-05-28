<?php
namespace Tuum\Web\Stack;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Tuum\Web\Middleware\AfterReleaseTrait;
use Tuum\Web\View\ErrorView;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\View\ViewEngineInterface;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\View\ViewStream;

class ViewStack implements MiddlewareInterface
{
    use MiddlewareTrait;

    use AfterReleaseTrait;

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
        $response = $this->next ? $this->next->__invoke($request) : null;

        if (is_null($response)) {
            // no response. turn it to not-found response.
            $response = $request->respond()->asNotFound();
        }
        elseif (is_string($response)) {
            // return as a plain text.
            $response = $request->respond()->asText($response);
        }
        elseif (is_array($response)) {
            // return as a JSON.
            $response = $request->respond()->asJson($response);
        }
        elseif (!$response instanceof ResponseInterface) {
            // what is this? just return it.
            return $response;
        }
        // post process $response for view.
        $response = $this->streamView($response);
        $response = $this->applyAfterReleases($request, $response);
        return $response;
    }

    /**
     * fill up contents for VIEW and ERROR responses.
     *
     * @param Response $response
     * @return Response
     */
    private function streamView($response)
    {
        if (Response::isError($response) &&
            !$response->getBody() instanceof ViewStream
        ) {
            $response = $this->setErrorView($response);
            return $response;
        }
        return $response;
    }

    /**
     * render error pages.
     *
     * @param Response $response
     * @return Response
     */
    private function setErrorView($response)
    {
        if (Response::isServerError($response) && $this->logger) {
            $this->logger->error('received a server error response: ' . $response->getStatusCode());
        }
        $stream   = $this->error->getStream($response->getStatusCode(), $response->getData());
        $response = $response->withBody($stream);
        return $response;
    }
}