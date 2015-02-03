<?php

/** @var Web $app */

use Tuum\Web\App;
use Tuum\Web\Web;

$engine = $app->get('error-renderer-service');
$stack = new \Tuum\Web\Stack\ErrorStack($engine, $app->get(App::DEBUG));
$stack->setLogger($app->get(App::LOGGER));

return $stack;