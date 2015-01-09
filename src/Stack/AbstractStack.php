<?php
namespace Tuum\Web\Stack;

abstract class AbstractStack implements WebHandleInterface
{
    use StackMatchTrait;

    use StackFilterTrait;
}