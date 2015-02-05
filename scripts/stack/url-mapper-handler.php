<?php
use Tuum\Locator\Locator;
use Tuum\Web\Stack\UrlMapper;
use Tuum\Web\App;
use Tuum\Web\Web;

/*
 * sample session stack constructor script for locator.
 */

/** @var Web $app */

$loc = new Locator($app->get(App::DOCUMENT_DIR));
return new UrlMapper($loc);