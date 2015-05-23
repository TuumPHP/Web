<?php
namespace Tuum\Web;

use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

/**
 * Interface ApplicationInterface
 *
 * defines the lambda style interface for http job handle.
 * this does not have to be a middleware.
 *
 * @package Tuum\Web
 */
interface FilterInterface extends ApplicationInterface
{
}