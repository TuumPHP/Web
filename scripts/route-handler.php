<?php

use Tuum\Web\Web;

/** @var Web $app */

$router = $app->get('router');
$route = \Tuum\Web\Stack\Routes::forge($router);

return $route;