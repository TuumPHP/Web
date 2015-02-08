<?php

use Aura\Session\SessionFactory;
use Tuum\Web\Stack\SessionStack;

/*
 * sample session stack constructor script for locator.
 */

/*
 * session manager.
 * use Aura/Session as default session manager.
 */
$session_factory = new SessionFactory;
$session         = $session_factory->newInstance($_COOKIE);

return new SessionStack($session);
