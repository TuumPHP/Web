<?php
namespace Tuum\Web\Stack;

abstract class AbstractStack implements StackHandleInterface
{
    use StackMatchTrait;

    use StackFilterTrait;
}