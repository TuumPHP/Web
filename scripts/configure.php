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
use Tuum\Web\App;

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
$dic->add(App::RENDER_ENGINE, function() use($dic) {

    return new Renderer(
        new Locator($dic->get(App::TEMPLATE_DIR))
    );
}, true);

/**
 * rendering error page. should overwrite this service.
 */
$dic->add('service/error-renderer', function () use ($dic) {

    $view = new ErrorView($dic->get(App::RENDER_ENGINE), $dic->get(App::DEBUG));
    $view->setLogger($dic->get(App::LOGGER));

    return $view;
});

/**
 * CsRf Filter
 */
$dic->add(App::CS_RF_FILTER, function () use ($dic) {
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
$dic->add(App::LOGGER, false, true);

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
    $stack  = new \Tuum\Web\Stack\ErrorStack($engine, $dic->get(App::DEBUG));
    $stack->setLogger($dic->get(App::LOGGER));
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
    $session = $factory->newInstance($_COOKIE);
    return new SessionStack($session);
});

/**
 * UrlMapperStack
 */
$dic->add('stack/url-mapper-handler', function () use ($dic) {

    $loc = new Locator($dic->get(App::DOCUMENT_DIR));
    return new UrlMapper($loc);
});

/**
 * ViewStack
 */
$dic->add('stack/view-stack', function () use ($dic) {

    $view = new View();
    return new ViewStack(
        $dic->get(App::RENDER_ENGINE),
        $view
    );
});

