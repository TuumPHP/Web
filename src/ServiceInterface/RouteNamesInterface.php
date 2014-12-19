<?php
namespace Tuum\Web\NamedRoutesInterface;

interface RouteNamesInterface
{
    /**
     * @param string $name
     * @param array  $args
     * @return string
     */
    public function get($name, $args);
}