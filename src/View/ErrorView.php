<?php
namespace Tuum\Web\View;

use Exception;
use Phly\Http\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Tuum\Web\Psr7\Respond;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Psr7\StreamFactory;

class ErrorView
{
    /**
     * @var ViewEngineInterface
     */
    private $engine;

    /**
     * default error file name.
     *
     * @var string
     */
    public $default_error_file = 'errors/error';

    /**
     * error file names for each status code.
     *
     * @var array
     */
    public $error_files = [];

    /**
     * @var null|LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param ViewEngineInterface $engine
     * @param bool                $debug
     */
    public function __construct($engine, $debug = false)
    {
        $this->engine = $engine;
        $this->debug  = $debug;
    }

    /**
     * error handler for production environment.
     * returns a response with error page.
     *
     * @param Exception $e
     * @return Response
     */
    public function __invoke($e)
    {
        $data['message'] = $e->getMessage();
        $code            = $e->getCode() ?: Respond::INTERNAL_ERROR;
        if ($this->logger) {
            $this->logger->critical('ErrorView: caught ' . get_class($e) . "({$code}), " . $e->getMessage(),
                $e->getTrace());
        }
        if ($this->debug) {
            $data['trace'] = $e->getTrace();
        }
        echo $this->getStream($code, $data);
        exit;
    }

    /**
     * @param int   $code
     * @param array $data
     * @return Stream|StreamInterface|ViewStream
     */
    public function getStream($code, $data = [])
    {
        $error = $this->findView($code);
        if ($error) {
            return $this->engine->getStream($error, $data);
        }
        return StreamFactory::string('<h1>Error</h1>');
    }
    
    /**
     * @param null|LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * find error-view file from error code ($code).
     *
     * @param $code
     * @return string
     */
    private function findView($code)
    {
        return isset($this->error_files[$code]) ? $this->error_files[$code] : $this->default_error_file;
    }
}