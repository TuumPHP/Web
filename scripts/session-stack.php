<?php

/*
 * for now. until session stack is implemented
 */

use Symfony\Component\HttpFoundation\Session\Session;
use Tuum\Web\Stack\SessionStack;
use Tuum\Web\App;

/*
 * sample session stack constructor script for locator.
 */

$session = new Session();
$session->start();
return new SessionStack($session);
