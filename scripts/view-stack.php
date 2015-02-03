<?php

use Tuum\Web\Stack\ViewStack;
use Tuum\Web\App;
use Tuum\Web\Web;

/*
 * sample session stack constructor script for locator.
 */

/** @var Web $app */

return new ViewStack($app->get(App::RENDER_ENGINE));
