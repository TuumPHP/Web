<?php
namespace Tuum\Web\Controller;

/**
 * Class ResourcefulControllerTrait
 *
 * Route patterns similar to Laravel's resource controller.
 *
 * @package Tuum\Web\Controller
 */
trait ResourcefulControllerTrait
{
    use RouteDispatchTrait;

    protected $routes = [
        'get:/'          => 'index',
        'get:/create'    => 'create',
        'post:/'         => 'insert',
        'get:/{id}'      => 'get',
        'get:/{id}/edit' => 'edit',
        'put:/{id}'      => 'update',
        'delete:/{id}'   => 'delete',
    ];

    /**
     * @return array
     */
    protected function getRoutes()
    {
        return $this->routes;
    }

}