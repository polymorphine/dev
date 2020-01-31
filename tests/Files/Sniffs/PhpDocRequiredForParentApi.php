<?php

namespace Polymorphine\CodeStandards\Tests\Files\Sniffs;


class PhpDocRequiredForParentApi
{
    public function overriddenMethodA() {}
    /** Documented */
    public function overriddenMethodB() {}
}
