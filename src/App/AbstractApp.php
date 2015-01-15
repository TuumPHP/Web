<?php
namespace Tuum\Web\App;

abstract class AbstractApp implements AppHandleInterface
{
    use MatchRootTrait;

    use BeforeFilterTrait;
}