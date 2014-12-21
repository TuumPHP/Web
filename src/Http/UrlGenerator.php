<?php
namespace Tuum\Web\Http;

class UrlGenerator
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var array
     */
    protected $with = [];

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * set request for testing purpose.
     *
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
     * @param string $path
     * @return UrlGenerator
     */
    public function __invoke($path = null)
    {
        $url         = clone($this);
        $url->url    = ''; // just in case.
        $url->secure = false;
        $url->with   = [];
        if ($path) {
            $url->to($path);
        }
        return $url;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function addSlashIfNot($path)
    {
        if (!$path) {
            return '/';
        }
        return substr($path, 0, 1) === '/' ? $path : '/' . $path;
    }

    /**
     * generate a URL w.r.t. the base URL.
     *
     * @param $path
     * @return $this
     */
    public function to($path)
    {
        $this->url = $this->getRequest()->getBaseUrl();
        $this->url .= $this->addSlashIfNot($path);
        return $this;
    }

    /**
     * generate a URL w.r.t. the original base URL.
     *
     * @param string $path
     * @return $this
     */
    public function base($path)
    {
        $this->url = $this->getRequest()->attributes->get('url.mapped') ?: $this->getRequest()->getBaseUrl();
        $this->url .= $this->addSlashIfNot($path);
        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function with($args)
    {
        foreach ($args as $key => $arg) {
            $args[$key] = urlencode($arg);
        }
        $this->with = array_merge($this->with, $args);
        return $this;
    }

    /**
     * @param bool $secure
     * @return $this
     */
    public function secure($secure = true)
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * @param $url
     * @return string
     */
    protected function addHttps($url)
    {
        $host = $this->getRequest()->getHost();
        return "https://{$host}{$url}";
    }

    /**
     * @param $url
     * @return string
     */
    protected function addArgs($url)
    {
        if (empty($this->with)) {
            return $url;
        }
        $list = [];
        foreach ($this->with as $key => $arg) {
            $list[] = "{$key}={$arg}";
        }
        return $url . '?' . implode('&amp;', $list);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $url = $this->addArgs($this->url);
        if ($this->secure) {
            return $this->addHttps($url);
        }
        return $url;
    }
}