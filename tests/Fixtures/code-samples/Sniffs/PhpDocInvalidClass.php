<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;


class PhpDocInvalidClass extends NotExistingParent
{
    public function undocumentedMethod() {}
    /** Documented */
    public function documentedMethod() {}
}
