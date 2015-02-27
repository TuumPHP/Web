<?php
namespace Tuum\Web;

use League\Container\Container;

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

    /**
     * @param array $config
     * @return Application
     */
    public static function getApp(array $config)
    {
        $app = new Application(
            new Container()
        );

        /*
         * set up directories.
         */
        $app->set(Web::CONFIG_DIR,   $config[Web::CONFIG_DIR]);
        $app->set(Web::TEMPLATE_DIR, $config[Web::TEMPLATE_DIR]);
        $app->set(Web::DOCUMENT_DIR, $config[Web::DOCUMENT_DIR]);
        $app->set(Web::VAR_DATA_DIR, $config[Web::VAR_DATA_DIR]);
        $app->set(Web::DEBUG,        $config[Web::DEBUG]);

        /*
         * load a default configuration. 
         */
        $app->configure(dirname(__DIR__).'/scripts/configure');
        
        return $app;
    }
}