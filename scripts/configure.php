<?php

use Aura\Session\SessionFactory;
use League\Container\Container;
use Tuum\Locator\Locator;
use Tuum\View\ErrorView;
use Tuum\View\Tuum\Renderer;
use Tuum\Web\Filter\CsRfFilter;
use Tuum\Web\Stack\UrlMapper;
use Tuum\Web\Stack\SessionStack;
use Tuum\Web\Stack\CsRfStack;
use Tuum\Web\Stack\ViewStack;
use Tuum\Web\Viewer\View;
use Tuum\Web\Web;

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
$dic->add(Web::RENDER_ENGINE, function() use($dic) {

    return new Renderer(
        new Locator($dic->get(Web::TEMPLATE_DIR))
    );
}, true);

/**
 * rendering error page. should overwrite this service.
 */
$dic->add('service/error-renderer', function () use ($dic) {

    $view = new ErrorView($dic->get(Web::RENDER_ENGINE), $dic->get(Web::DEBUG));
    $view->setLogger($dic->get(Web::LOGGER));

    return $view;
});

/**
 * CsRf Filter
 */
$dic->add(Web::CS_RF_FILTER, function () use ($dic) {
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
$dic->add(Web::LOGGER, false, true);

/**
 * CsRf Stack
 *
 * check for all the post requests.
 */
$dic->add('stack/cs-rf-stack', function () use ($dic) {

    $stack = new CsRfStack();
    $stack->setRoot('post:/*');
    return $stack;
});

/**
 * ErrorStack
 */
$dic->add('stack/error-stack', function () use ($dic) {

    $engine = $dic->get('service/error-renderer');
    $stack  = new \Tuum\Web\Stack\ErrorStack($engine, $dic->get(Web::DEBUG));
    $stack->setLogger($dic->get(Web::LOGGER));
    return $stack;
});

/**
 * SessionStack
 *
 * session manager.
 * use Aura/Session as default session manager.
 */
$dic->add('stack/session-stack', function () use ($dic) {

    $factory = new SessionFactory;
    return new SessionStack($factory);
});

/**
 * UrlMapperStack
 */
$dic->add('stack/url-mapper-handler', function () use ($dic) {

    $loc = new Locator($dic->get(Web::DOCUMENT_DIR));
    return new UrlMapper($loc);
});

/**
 * ViewStack
 */
$dic->add('stack/view-stack', function () use ($dic) {

    $view = new View();
    return new ViewStack(
        $dic->get(Web::RENDER_ENGINE),
        $view
    );
});

/**
 * stack list.
 *
 * return list of stacks to push.
 *
 */
$dic->add('stacks', function () {
    return [
        /*
         * basic stack
         */
        'stack/error-stack',
        'stack/session-stack',
        'stack/cs-rf-stack',
        'stack/view-stack',
        'stack/url-mapper-handler',
    ];
});