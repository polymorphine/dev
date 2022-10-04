<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;


class PhpDocRequiredForInvalidClass extends NotExistingParent
{
    public function undocumentedMethod() {}
    /** Documented */
    public function documentedMethod() {}
}
