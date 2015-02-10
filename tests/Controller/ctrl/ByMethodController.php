<?php
namespace tests\Controller\ctrl;

use Tuum\Web\Controller\AbstractController;
use Tuum\Web\Controller\DispatchByMethodTrait;
use Tuum\Web\Psr7\Response;

class ByMethodController extends AbstractController
{
    use DispatchByMethodTrait;

    /**
     * @return Response
     */
    protected function onPut()
    {
        return $this->respond
            ->asText('on-put');
    }
}
