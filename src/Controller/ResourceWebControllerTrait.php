<?php
namespace Tuum\Web\Controller;

/**
 * Class ResourceWebControllerTrait
 *
 * Route patterns for general web interactions.
 *
 *
 * @package Tuum\Web\Controller
 */
trait ResourceWebControllerTrait
{
    use RouteDispatchTrait;

    protected $routes = [
        'get:/'             => 'index',
        'get:/create'       => 'create',
        'post:/create'      => 'verifyCreate',
        'post:/insert'      => 'insert',
        'get:/{id}'         => 'get',
        'get:/{id}/edit'    => 'edit',
        'post:/{id}/edit'   => 'verifyEdit',
        'post:/{id}/update' => 'update',
        'get:/{id}/delete'  => 'verifyDelete',
        'post:/{id}/delete' => 'delete',
    ];

    /**
     * @return array
     */
    protected function getRoutes()
    {
        return $this->routes;
    }

}