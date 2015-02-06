<?php

use Symfony\Component\HttpFoundation\Session\Session;
use Tuum\Web\Stack\SessionStack;

/*
 * sample session stack constructor script for locator.
 */

$session = new Session();
return new SessionStack($session);
