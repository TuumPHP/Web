<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Http\Request;

/**
 * Class Pile
 * @package WScore\Pile
 *
 * creates a pile of handlers for http request.
 * continues processing the request until one of the pile returns a response.
 */
class Stack implements StackableInterface
{
    use StackableTrait;

    /**
     * overwrite this method.
     *
     * @param Request $request
     * @return bool
     */
    protected function isMatch(
        /** @noinspection PhpUnusedParameterInspection */
        $request
    ) {
        return true;
    }

    /**
     * overwrite this method.
     *
     * @param $request
     * @return null
     */
    protected function applyFilters(
        /** @noinspection PhpUnusedParameterInspection */
        $request
    ) {
        return null;
    }
}