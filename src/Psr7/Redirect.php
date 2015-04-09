<?php
namespace Tuum\Web\Psr7;

use Psr\Http\Message\UriInterface;

class Redirect extends AbstractResponseFactory
{

    /**
     * redirects to $uri.
     * the $uri must be a full uri (like http://...), or a UriInterface object.
     *
     * @param UriInterface|string $uri
     * @return Response
     */
    public function toAbsoluteUri($uri)
    {
        return Response::redirect($uri, $this->data);
    }

    /**
     * redirects to a path in string.
     * uses current hosts and scheme.
     *
     * @param string $path
     * @return Response
     */
    public function toPath($path)
    {
        $uri = $this->request->getUri()->withPath($path);
        return $this->toAbsoluteUri($uri);
    }

    /**
     * @param string $path
     * @return Response
     */
    public function toBasePath($path = '')
    {
        $path = '/' . ltrim($path, '/');
        $path = $this->request->getBasePath() . $path;
        $uri  = $this->request->getUri()->withPath($path);
        return $this->toAbsoluteUri($uri);
    }

}
