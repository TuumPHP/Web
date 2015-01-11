<?php
namespace Tuum\Web\App;

abstract class AbstractApp implements AppHandleInterface
{
    use AppMatchTrait;

    use AppFilterTrait;
}