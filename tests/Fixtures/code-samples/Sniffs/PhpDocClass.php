<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;

use Closure;


class PhpDocClass extends PhpDocParent implements PhpDocInterface
{
    public static function staticConstructor(): self { return new self(); }

    public $value;

    public function overriddenMethodA() {}
    private function nonApiMethod() {}
    /** no warning in next line */
    public function originalMethodWithDoc() {}
    public function originalMethodWithoutDoc() {}
    public function originalMethodWithoutRequiredDoc(): Closure { return fn() => false; }
    public function interfaceMethodA(int $value): bool { return true; }
    public function shouldBeDocumented(bool $foo): void {}
    public function methodRequiresDoc(array $test): PhpDocInterface { return $this; }
}
