<?php
namespace Tuum\Web\Http;

use Aura\Router\Router;
use Tuum\Stack\Http\Redirect;
use Tuum\Stack\Http\Respond as BaseRespond;
use Tuum\Web\App;

class Respond extends BaseRespond
{

    /**
     * @param string $name
     * @return Redirect
     */
    public function named($name)
    {
        $router = $this->request->attributes->get(App::ROUTE_NAMES);
        if (!$router) {
            throw new \BadMethodCallException('no named routes');
        }
        $url  = null;
        $args = func_get_args();
        array_shift($args);
        if ($router instanceof Router) {
            $url = $router->generate($name, $args);
        }
        if (!$url) {
            throw new \InvalidArgumentException('no such named routes: ' . $name);
        }
        return new Redirect($url);
    }

}