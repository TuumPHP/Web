<?php
namespace Tuum\Web\Http;

class Respond
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var string
     */
    protected $error_file = 'error';

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
     * @param string $error_file
     */
    public function setErrorFile($error_file)
    {
        $this->error_file = $error_file;
    }

    /**
     * @param string $content
     * @param string $charset
     * @return Response
     */
    public function text($content, $charset='UTF-8')
    {
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/html; charset='.$charset);
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
        $response->fill($this->request->attributes->get('data'));
        return $response;
    }

    /**
     * @param int    $status
     * @param string $file
     * @return View
     */
    public function error($status = Response::HTTP_INTERNAL_SERVER_ERROR, $file = null)
    {
        if (!$file) {
            $file = $this->error_file;
        }
        $response = new View('', $status);
        $response->setFile($file);
        $response->fill($this->request->attributes->get('data'));
        return $response;
    }

    /**
     * @param null $file
     * @return View
     */
    public function notFound($file = null)
    {
        $response = $this->error(Response::HTTP_NOT_FOUND, $file);
        return $response;
    }

}