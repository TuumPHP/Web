<?php
namespace Tuum\Web\Stack;

use Tuum\Web\Http\Response;
use Tuum\Web\Http\Request;


trait StackFilterTrait
{

    /**
     * @var array
     */
    protected $beforeFilters = [];

    /**
     * @param string $filter
     * @return $this
     */
    public function before($filter)
    {
        $this->beforeFilters[] = $filter;
        return $this;
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    protected function applyFilters($request)
    {
        foreach ($this->beforeFilters as $filter) {
            if ($response = $request->filter($filter)) {
                return $response;
            }
        }
        return null;
    }

}