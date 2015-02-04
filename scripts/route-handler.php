<?php

use Tuum\Web\Stack\Dispatcher;
use Tuum\Web\Stack\RouterStack;
use Tuum\Web\Web;

/** @var Web $app */

$router = $app->get('router');
return new RouterStack($router, new Dispatcher($app));
