<?php
namespace Tuum\Web\Http;

use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirect;

class RedirectResponse extends BaseRedirect
{
    use ResponseWithTrait;
}