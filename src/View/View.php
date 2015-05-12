<?php
namespace Tuum\Web\View;

use Closure;
use Psr\Http\Message\StreamInterface;
use Tuum\Locator\Locator;
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
     * @var ViewStream
     */
    private $stream;
    
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
     * @param string $view_dir
     * @return View
     */
    public static function forge($view_dir)
    {
        $self = new View(
            new Renderer(new Locator($view_dir)),
            new Value()
        );
        $self->stream = new ViewStream($self);
        return $self;
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
     * a simple renderer for a raw PHP file.
     *
     * @param string|callable $file
     * @param array  $data
     * @return StreamInterface|ViewStream
     * @throws \Exception
     */
    public function getStream($file, $data = [])
    {
        $stream = $this->stream ? clone($this->stream) : new ViewStream($this);
        $stream->setView($file, $data);
        return $stream;
    }
        
    /**
     * @param Closure $modifiers
     */
    public function modRenderer($modifiers)
    {
        $modifiers($this->renderer);
    }
}
