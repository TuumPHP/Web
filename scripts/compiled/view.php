<?php
use Tuum\Locator\Locator;
use Tuum\View\Renderer;
use Tuum\Web\View\Value;
use Tuum\Web\View\View;

$view = new View(new Renderer(new Locator()), new Value());
