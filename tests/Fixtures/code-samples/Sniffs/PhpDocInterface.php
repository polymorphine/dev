<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;


interface PhpDocInterface
{
    /**
     * Whatever - no content check
     */
    public function interfaceMethodA(int $value): bool;
    public function shouldBeDocumented(bool $foo): void;
    public function methodRequiresDoc(array $test): self;
    public function requiredDoc(): \Generator;
}
