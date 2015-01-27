<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\App\AppMarkerInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

class Chain extends Stackable
{
    /**
     * @var AppMarkerInterface[]
     */
    protected $apps = [];

    /**
     * @param AppHandleInterface $app
     * @return $this
     */
    public function addApp($app)
    {
        $apps       = func_get_args();
        $this->apps = array_merge($this->apps, $apps);
        return $this;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return null|Response
     */
    public function execute($request, $response)
    {
        foreach ($this->apps as $app) {

            if (method_exists($app, 'isMatch') && !$app->isMatch($request)) {
                continue;
            }
            if (!$response && $app instanceof AppHandleInterface) {
                if (method_exists($app, 'filterBefore')) {
                    $response = $app->filterBefore($request);
                    if ($response) {
                        continue;
                    }
                }
                if (!$response) {
                    $response = $app->__invoke($request);
                }
            }
        }
        return $response;
    }
}