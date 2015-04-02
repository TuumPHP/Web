<?php
namespace Tuum\Web\Stack;

use Psr\Log\LoggerInterface;
use Tuum\View\ErrorView;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;
use Tuum\Web\Psr7\StreamFactory;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorStack implements MiddlewareInterface
{
    use MiddlewareTrait;
    
    /**
     * @var ErrorView
     */
    protected $renderer;

    /**
     * @var null|LoggerInterface
     */
    protected $logger;
    
    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param ErrorView  $renderer
     * @param bool       $debug
     */
    public function __construct($renderer, $debug=false)
    {
        $this->renderer = $renderer;
        $this->debug    = $debug;

        /*
         * set up whoops and others.
         */
        $whoops = new Run;
        if($this->debug) {
            error_reporting(E_ALL);
            $whoops->pushHandler(new PrettyPageHandler);
        } else {
            error_reporting(E_ERROR);
            $whoops->pushHandler($this->renderer);
        }
        $whoops->register();

    }

    /**
     * @param Request          $request
     * @param callable|null    $next
     * @return null|Response
     */
    public function __invoke($request, $next=null)
    {
        /*
         * set up error view, or whoops if debug is true.
         */
        /*
         * execute the subsequent stack.
         */
        $response = $this->execNext($request);

        /*
         * check for error response. 
         */
        if( !$response ) {
            $response = $request->respond()->asNotFound();
        }
        if( !$response->isType(Response::TYPE_ERROR)) {
            return $response;
        }
        if( $this->logger ) {
            $this->logger->error('ErrorRelease: received an error response: '.$response->getStatusCode());
        }
        $content = $this->renderer->render($response->getStatusCode(), $response->getData());
        $response = $response->withBody(StreamFactory::string($content));
        return $response;
    }

    /**
     * @param null|LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}