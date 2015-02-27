<?php

use League\Container\Container;
use Tuum\Locator\Locator;
use Tuum\Web\Application;

/** --------------------------------------------------------------------------+
 * DI container and Web Application
 *
 */

return new Application(
    new Locator(),
    new Container()
);
