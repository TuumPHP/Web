<?php

use League\Container\Container;
use Tuum\Locator\Locator;
use Tuum\Web\Web;

/** --------------------------------------------------------------------------+
 * DI container and Web Application
 *
 */

return new Web(
    new Locator(),
    new Container()
);
