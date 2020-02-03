<?php

namespace Polymorphine\CodeStandards\Tests\CodeSamples\Sniffs;


class PhpDocRequiredForInvalidClass extends NotExistingParent
{
    public function undocumentedMethod() {}
    /** Documented */
    public function documentedMethod() {}
}
