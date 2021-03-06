<?php
namespace Tuum\Web\Controller;

/**
 * Class ResourceControllerTrait
 *
 * Route patterns for resource type using only get and post methods.
 *
 * @package Tuum\Web\Controller
 */
trait ResourceControllerTrait
{
    use RouteDispatchTrait;

    protected $routes = [
        'get:/create'       => 'create',
        'get:/{id}/edit'    => 'edit',
        'get:/{id}'         => 'get',
        'get:/'             => 'index',
        'post:/{id}/delete' => 'delete',
        'post:/{id}'        => 'update',
        'post:/'            => 'insert',
    ];

    /**
     * @return array
     */
    protected function getRoutes()
    {
        return $this->routes;
    }

}