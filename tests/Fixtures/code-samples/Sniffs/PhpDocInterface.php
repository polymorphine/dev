<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;


interface PhpDocInterface
{
    /**
     * Whatever - no content check
     */
    public function interfaceMethodA(int $value): bool;
    public function interfaceMethodB(array $test): self;
}
