<?php
namespace Tuum\Web\Http;

class Redirect
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
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $url
     * @return RedirectResponse
     */
    public function location($url)
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
        return $this->location((string)$url);
    }

    /**
     * @param string $url
     * @return RedirectResponse
     */
    public function reload($url = null)
    {
        $url = $this->request->getPathInfo($url);
        return $this->location((string)$url);
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