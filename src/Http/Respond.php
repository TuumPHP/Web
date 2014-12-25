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
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $content
     * @return Response
     */
    public function text($content)
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
     * @param string $url
     * @return RedirectResponse
     */
    public function redirect($url)
    {
        return new RedirectResponse($url);
    }

    /**
     * @param string $url
     * @return RedirectResponse
     */
    public function to($url = null)
    {
        $url = $this->request->url()->to($url);
        return $this->redirect((string)$url);
    }

    /**
     * @param string $url
     * @return RedirectResponse
     */
    public function reload($url = null)
    {
        $url = $this->request->getPathInfo($url);
        return $this->redirect((string)$url);
    }

    /**
     * @param string $file
     * @return View
     */
    public function view($file)
    {
        $response = new View();
        $response->setFile($file);
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

    /**
     * @param string $name
     * @param array  $args
     * @return RedirectResponse
     */
    public function named($name, $args)
    {
        $url = $this->request->named($name, $args);
        return new RedirectResponse($url);
    }

}