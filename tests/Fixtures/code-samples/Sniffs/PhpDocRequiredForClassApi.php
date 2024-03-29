<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;


class PhpDocRequiredForClassApi extends PhpDocRequiredForParentApi implements PhpDocRequiredForInterfaceApi
{
    public $value;

    public function overriddenMethodA() {}
    private function nonApiMethod() {}
    /** no warning in next line */
    public function originalMethodWithDoc() {}
    public function originalMethodWithoutDoc() {}
    public function interfaceMethodA(int $value): bool { return true; }
    public function interfaceMethodB(array $test): PhpDocRequiredForInterfaceApi { return $this; }
    public static function staticConstructor(): self { return new self(); }
}
