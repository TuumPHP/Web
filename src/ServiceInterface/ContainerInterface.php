<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 15/01/04
 * Time: 8:05
 */
namespace Tuum\Web\ServiceInterface;

interface ContainerInterface
{
    /**
     * a simple container based on evaluating a file.
     * closures will be evaluated each time.
     *
     * @param string $file
     * @param array  $data
     * @return mixed
     */
    public function get($file, $data = []);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);
}