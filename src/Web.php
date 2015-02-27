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
class Web
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
     * services and filters
     */
    const LOGGER = 'logger';
    const ROUTE_NAMES = 'namedRoutes';
    const RENDER_ENGINE = 'renderer';
    const CS_RF_FILTER = 'csrf';

}