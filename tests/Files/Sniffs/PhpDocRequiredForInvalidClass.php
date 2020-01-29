<?php

namespace Polymorphine\CodeStandards\Tests\Files\Sniffs;


class PhpDocRequiredForInvalidClass extends NotExistingParent
{
    public function undocumentedMethod() {}
    /** Documented */
    public function documentedMethod() {}
}
