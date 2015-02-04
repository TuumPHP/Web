<?php
namespace Tuum\Web\Controller;

trait ResourceControllerTrait
{
    use RouteDispatchTrait;

    protected $routes = [
        'get:/'           => 'index',
        'get:/create'     => 'create',
        'post:/'          => 'post',
        'get:/{id}'       => 'get',
        'put:/{id}'       => 'put',
        'get:/{id}/edit'  => 'edit',
        'post:/{id}'      => 'update',
        'delete:/{id}'    => 'delete',
    ];

    /**
     * @return array
     */
    protected function getRoutes()
    {
        return $this->routes;
    }

}