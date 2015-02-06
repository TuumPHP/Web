<?php

use Tuum\Web\Stack\CsRfStack;

$stack = new CsRfStack();
$stack->setRoot('post:/*');

return $stack;