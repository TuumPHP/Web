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
    const ROUTES_FILE  = 'file.routes';
    const CONFIG_DIR   = 'dir.config';
    const TEMPLATE_DIR = 'dir.view';
    const DOCUMENT_DIR = 'dir.resource';
    const VAR_DATA_DIR = 'dir.variable';

    const DEBUG = 'debug';
    const LOGGER = 'logger';
    const VIEW_DATA = 'data';
    const FLASH_NAME = 'flash';
    const ROUTE_PARAM = 'params';
    const ROUTE_NAMES = 'namedRoutes';
    const CONTROLLER  = 'controller';
    const RENDER_ENGINE = 'renderer';
    const ROUTER = 'router';

    const SESSION_MGR = 'session.manager';
    const TOKEN_NAME = '_token';

}