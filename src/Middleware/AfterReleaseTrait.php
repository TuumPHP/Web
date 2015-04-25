<?php
namespace Tuum\Web\Middleware;

use Closure;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\ReleaseInterface;

trait AfterReleaseTrait
{
    /**
     * list of filters to apply if matched.
     *
     * @var string[]|Closure[]|ReleaseInterface[]
     */
    protected $_afterRelease = [];

    /**
     * @param string|Closure|ReleaseInterface $release
     */
    public function setAfterRelease($release)
    {
        $this->_afterRelease[] = $release;
    }

    /**
     * @param Request       $request
     * @param null|Response $response
     * @return null|Response
     */
    protected function applyAfterReleases($request, $response)
    {
        foreach ($this->_afterRelease as $release) {
            if (!$release = $request->getFilter($release)) {
                continue;
            }
            $response = $release($request, $response);
        }
        return $response;
    }
}