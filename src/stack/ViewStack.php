<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\App;
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
        $data = $response->getData();
        if( !isset($data['_request'])) {
            $data['_request'] = $request;
        }
        // generate C.S.R.F. token
        if(!isset($data['_token'])) {
            $data['_token'] = $this->calToken();
        }
        $response = $response->withData(App::TOKEN_NAME, $data['_token']);
        // render view file. 
        $file = $response->getViewFile();
        $content = $this->engine->render($file, $data);
        $response = $response->withBody(StreamFactory::string($content));
        return $response;
    }
    
    protected function calToken()
    {
        return sha1(uniqid() . mt_rand(0, 10000));
    }
}