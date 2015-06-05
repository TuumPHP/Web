<?php
namespace Tuum\Web\View;

use Closure;
use Psr\Http\Message\StreamInterface;

/**
 * Interface RendererInterface
 *
 * an interface for rendering a view file or a template.
 *
 * @package Tuum\View
 */
interface ViewEngineInterface
{
    /**
     * a simple renderer for a raw PHP file.
     *
     * @param string|callable $file
     * @param array  $data
     * @return string
     * @throws \Exception
     */
    public function render($file, $data = []);

    /**
     * a simple renderer for a raw PHP file.
     *
     * @param string|callable $file
     * @param array  $data
     * @return StreamInterface|ViewStream
     * @throws \Exception
     */
    public function getStream($file, $data = []);
    
    /**
     * modifies renderer using closure:
     * function($renderer) {}, where $renderer is the view engine. 
     * 
     * @param Closure $modifiers
     */
    public function modRenderer($modifiers);
}