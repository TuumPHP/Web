<?php

use Tuum\Web\Web;

/** @var Web $app */

$router = $app->get('router');
$route = \Tuum\Web\Stack\Router::forge($router);

return $route;