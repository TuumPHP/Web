<?php

use League\Container\Container;
use Tuum\Web\Application;

/** --------------------------------------------------------------------------+
 * DI container and Web Application
 *
 */

return new Application(
    new Container()
);
