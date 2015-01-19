<?php
namespace Tuum\Web\Http;

use Tuum\Web\App;

class Respond
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @param string $content
     * @param string $charset
     * @return Response
     */
    public function text($content, $charset='UTF-8')
    {
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/plain; charset='.$charset);
        return $response;
    }

    /**
     * @param $content
     * @return Response
     */
    public function html($content)
    {
        $response = new Response($content);
        return $response;
    }

    /**
     * return json string.
     *
     * @param $data
     */
    public function json($data)
    {
        // todo: implement this method.
    }

    /**
     * issue a sub request to itself.
     *
     * @param $request
     */
    public function subRequest($request)
    {
        // todo: implement this method.
    }

    /**
     * @param string $file
     * @return View
     */
    public function view($file)
    {
        $response = new View();
        $response->setFile($file);
        $response->fill($this->request->attributes->get(App::VIEW_DATA));
        return $response;
    }

    /**
     * @param int    $status
     * @return Response
     */
    public function error($status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $response = new Response('', $status);
        $response->fill($this->request->attributes->get(App::VIEW_DATA));
        return $response;
    }

    /**
     * @return Response
     */
    public function notFound()
    {
        $response = $this->error(Response::HTTP_NOT_FOUND);
        return $response;
    }

}