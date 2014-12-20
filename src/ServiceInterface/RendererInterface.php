<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 14/12/16
 * Time: 11:24
 */
namespace Tuum\Web\ServiceInterface;

/**
 * Interface RendererInterface
 * 
 * an interface for rendering a view file or a template. 
 *
 * @package Tuum\Web\ServiceInterface
 */
interface RendererInterface
{
    /**
     * @param string $name
     * @param mixed  $service
     */
    public function register($name, $service);

    /**
     * a simple renderer for a raw PHP file.
     *
     * @param string $file
     * @param array  $data
     * @throws \Exception
     */
    public function render($file, $data = []);
}