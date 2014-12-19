<?php
namespace Tuum\Web\NamedRoutesInterface;

interface NamedRoutesInterface
{
    /**
     * @param string $name
     * @param array  $args
     * @return string
     */
    public function get($name, $args);
}