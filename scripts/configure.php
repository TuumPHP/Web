<?php

use Aura\Session\SessionFactory;
use League\Container\Container;
use Tuum\Form\Dates;
use Tuum\Form\Forms;
use Tuum\Locator\Locator;
use Tuum\Router\Tuum\Router;
use Tuum\View\ErrorView;
use Tuum\View\Tuum\Renderer;
use Tuum\Web\Application;
use Tuum\Web\Filter\CsRfFilter;
use Tuum\Web\Stack\Dispatcher;
use Tuum\Web\Stack\ErrorStack;
use Tuum\Web\Stack\RouterStack;
use Tuum\Web\Stack\UrlMapper;
use Tuum\Web\Stack\SessionStack;
use Tuum\Web\Stack\CsRfStack;
use Tuum\Web\Stack\ViewStack;
use Tuum\Web\Viewer\View;
use Tuum\Web\Web;

/** @var Application $app */
/** @var Container $dic */
if (!isset($dic)) {
    throw new RuntimeException('must set container as $dic.');
}

/** --------------------------------------------------------------------------+
 *   Set Up Filters and Services
 */

/**
 * Rendering Engine (Template)
 *
 * default is Tuum's view engine.
 * use it as a singleton.
 */
$app->set(Web::RENDER_ENGINE, function() use($dic) {

    $view = new Renderer(
        new Locator($dic->get(Web::TEMPLATE_DIR))
    );
    if($doc_root = $dic->get(Web::DOCUMENT_DIR)) {
        // also render php documents
        $view->locator->addRoot($doc_root);
    }
    $view->register('forms', new Forms());
    $view->register('dates', new Dates());
    return $view;
}, true);

/**
 * rendering error page. should overwrite this service.
 */
$app->set('service/error-renderer', function () use ($dic) {

    $view = new ErrorView($dic->get(Web::RENDER_ENGINE), $dic->get(Web::DEBUG));
    $view->setLogger($dic->get(Web::LOGGER));

    return $view;
});

/**
 * CsRf Filter
 */
$app->set(Web::CS_RF_FILTER, function () use ($dic) {
    return new CsRfFilter();
});


/** --------------------------------------------------------------------------+
 *   Set Up Stacks
 */

/**
 * Logger
 *
 * set to NULL as a default logger
 */
$app->set(Web::LOGGER, false, true);

/**
 * CsRf Stack
 *
 * check for all the post requests.
 */
$app->set('stack/cs-rf-stack', function () use ($dic) {

    $stack = new CsRfStack();
    $stack->setRoot('post:/*');
    return $stack;
});

/**
 * ErrorStack
 */
$app->set('stack/error-stack', function () use ($dic) {

    $engine = $dic->get('service/error-renderer');
    $stack  = new ErrorStack($engine, $dic->get(Web::DEBUG));
    $stack->setLogger($dic->get(Web::LOGGER));
    return $stack;
});

/**
 * SessionStack
 *
 * session manager.
 * use Aura/Session as default session manager.
 */
$app->set('stack/session-stack', function () use ($dic) {

    $factory = new SessionFactory;
    return new SessionStack($factory);
});

/**
 * UrlMapperStack
 */
$app->set('stack/url-mapper-handler', function () use ($dic) {

    $doc_root = $dic->get(Web::DOCUMENT_DIR);
    if(!$doc_root) {
        return null; // do not create url-mapper-stack.
    }
    $loc = new Locator($doc_root);
    return new UrlMapper($loc);
});

/**
 * ViewStack
 */
$app->set('stack/view-stack', function () use ($dic) {

    $view = new View();
    return new ViewStack(
        $dic->get(Web::RENDER_ENGINE),
        $view
    );
});

/**
 * RouterStack
 * 
 * a default rendering stack using Tuum's Router
 */
$app->set(Web::ROUTER_STACK, function() use ($app) {
    
    $router = Router::forge();
    return new RouterStack($router, new Dispatcher($app));
});

/**
 * stack list.
 *
 * return list of stacks to push.
 *
 */
$app->set('stacks', function () {
    return [
        'stack/error-stack',
        'stack/session-stack',
        'stack/cs-rf-stack',
        'stack/view-stack',
        'stack/url-mapper-handler',
    ];
});