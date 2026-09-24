<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;


class PhpDocParent extends PhpDocAncestor
{
    public static function parentConstructorNoDoc(): self { return new self(); }

    public function overriddenMethodA() {}
    /** Documented */
    public function overriddenMethodB() {}
}
