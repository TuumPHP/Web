<?php
namespace Tuum\Web\Http;

use Symfony\Component\HttpFoundation\RedirectResponse;

class Redirect extends RedirectResponse
{
    use ResponseWithTrait;
}