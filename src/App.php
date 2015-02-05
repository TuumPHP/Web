<?php
namespace Tuum\Web;

/**
 * Class App
 *
 * @package Tuum\Web
 *          web application.
 *
 *
 *          
 */
class App
{
    /*
     * directories
     */
    const CONFIG_DIR   = 'dir.config';
    const TEMPLATE_DIR = 'dir.view';
    const DOCUMENT_DIR = 'dir.resource';
    const VAR_DATA_DIR = 'dir.variable';

    /*
     * values
     */
    const DEBUG = 'debug';
    const TOKEN_NAME = '_token';

    /*
     * services
     */
    const LOGGER = 'logger';
    const ROUTE_NAMES = 'namedRoutes';
    const RENDER_ENGINE = 'renderer';
    const ROUTER = 'router';
    const SESSION_MGR = 'session.manager';

}