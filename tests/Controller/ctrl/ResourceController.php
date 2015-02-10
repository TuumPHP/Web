<?php
namespace tests\Controller\ctrl;

use Tuum\Web\Controller\AbstractController;
use Tuum\Web\Controller\DispatchByMethodTrait;
use Tuum\Web\Controller\ResourceControllerTrait;
use Tuum\Web\Psr7\Response;

class ResourceController extends AbstractController
{
    use ResourceControllerTrait;

    /**
     * @param string $id
     * @return Response
     */
    protected function onGet($id)
    {
        return $this->respond
            ->asText('on-get:'.$id);
    }
}
