<?php
namespace Tuum\Web\View;

use Closure;
use Tuum\View\Renderer;

class View implements ViewEngineInterface
{
    /**
     * @var ViewEngineInterface
     */
    private $renderer;

    /**
     * @var Value
     */
    private $value;

    /**
     * @param Renderer   $renderer
     * @param null|Value $value
     */
    public function __construct($renderer, $value = null)
    {
        $this->renderer = $renderer;
        $this->value    = $value;
    }

    /**
     * a simple renderer for a raw PHP file.
     *
     * @param string|callable $file
     * @param array  $data
     * @return string
     * @throws \Exception
     */
    public function render($file, $data = [])
    {
        if ($this->value) {
            $view = $this->value->withData($data);
            $data = ['view' => $view];
        }
        return $this->renderer->render($file, $data);
    }

    /**
     * @param Closure $modifiers
     */
    public function modRenderer($modifiers)
    {
        $modifiers($this->renderer);
    }
}
