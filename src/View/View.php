<?php
namespace Tuum\Web\View;

use Tuum\Locator\Locator;
use Tuum\View\Renderer;
use Tuum\View\ViewEngineInterface;

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
     * @var Locator
     */
    public $locator;

    /**
     * @param Renderer   $renderer
     * @param null|Value $value
     */
    public function __construct($renderer, $value = null)
    {
        $this->renderer = $renderer;
        $this->locator  = $renderer->locator; // bad!
        $this->value    = $value;
    }

    /**
     * a simple renderer for a raw PHP file.
     *
     * @param string $file
     * @param array  $data
     * @return string
     * @throws \Exception
     */
    public function render($file, $data = [])
    {
        if ($this->value) {
            $view = $this->value->withData($data);
            $data = ['view' => $view];
            if(isset($view->composer) && is_callable($view->composer)) {
                $composer = $view->composer;
                $this->renderer = $composer($this->renderer);
            }
        }
        return $this->renderer->render($file, $data);
    }

    /**
     * set layout file.
     *
     * @param string $file
     * @param array  $data
     * @return $this
     */
    public function setLayout($file, $data = [])
    {
        $this->renderer->setLayout($file, $data);
        return $this;
    }

    /**
     * set root directory of template files.
     *
     * @param $dir
     * @return $this
     */
    public function setRoot($dir)
    {
        $this->renderer->setRoot($dir);
        return $this;
    }
}
