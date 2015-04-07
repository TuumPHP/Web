<?php
namespace tests\Controller\ctrl;

use Tuum\Web\Controller\AbstractController;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;

class TestController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response|null;
     */
    protected function dispatch($request)
    {
        return $this->respond()
            ->withMessage('dispatched')
            ->withNotice('noticed')
            ->withError('withoutError')
            ->withInput(['input'=>'tested'])
            ->withInputErrors(['has'=>'error'])
            ->asView('responded');
    }
}