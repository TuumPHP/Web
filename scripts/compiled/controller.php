<?php
namespace Tuum\Web\scripts\compile;

use Tuum\Web\Controller\AbstractController;
use Tuum\Web\Controller\ResourceControllerTrait;

class MyController extends AbstractController
{
    use ResourceControllerTrait;

    function onGet() {
        return null;
    }

}

return new MyController();