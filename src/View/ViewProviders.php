<?php
namespace Tuum\Web\View;

use Tuum\Web\Application;
use Tuum\Web\Web;

class ViewProviders
{
    /**
     * @var Web
     */
    private $web;

    /**
     * @var Application
     */
    private $app;

    /**
     * @param Web $web
     */
    public function __construct($web)
    {
        $this->setWeb($web);
    }

    /**
     * @param Web $web
     * @return $this
     */
    public function setWeb($web)
    {
        $this->web = $web;
        $this->app = $web->getApp();
        return $this;
    }

    /**
     * get shared view engine, Renderer as default.
     *
     * @return ViewEngineInterface|View
     */
    public function getViewEngine()
    {
        if($this->app->exists(ViewEngineInterface::class)) {
            return $this->app->get(ViewEngineInterface::class);
        }
        $view = View::forge($this->web->view_dir);
        $this->app->set(ViewEngineInterface::class, $view, true);
        return $view;
    }

    /**
     * get error view render, ErrorView,
     *
     * @return ErrorView|null
     */
    public function getErrorView()
    {
        if($this->app->exists(ErrorView::class)) {
            return $this->app->get(ErrorView::class);
        }
        $error_files = (array)$this->app->get(Web::ERROR_VIEWS);
        if (empty($error_files)) {
            $this->app->set(ErrorView::class, null, true);
            return null;
        }
        $view = new ErrorView($this->getViewEngine(), $this->web->debug);
        if (isset($error_files[0])) {
            $view->default_error_file = $error_files[0];
            unset($error_files[0]);
        }
        $view->setLogger($this->web->getLog());
        $view->error_files = $error_files;
        $this->app->set(ErrorView::class, $view, true);

        return $view;
    }
}