<?php
namespace Tuum\Web\Stack;

use Tuum\Web\App\AppHandleInterface;
use Tuum\Web\Http\Request;
use Tuum\Web\Http\Response;

class Chain extends Stackable
{
    /**
     * @var AppHandleInterface[]
     */
    protected $apps = [];

    /**
     * @param AppHandleInterface $app
     * @return $this
     */
    public function addApp($app)
    {
        $apps = func_get_args();
        $this->apps = array_merge($this->apps, $apps);
        return $this;
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function execute($request)
    {
        foreach($this->apps as $app) {
            $response = $app->__invoke($request);
            if($response) {
                return $response;
            }
        }
        return null;
    }
}