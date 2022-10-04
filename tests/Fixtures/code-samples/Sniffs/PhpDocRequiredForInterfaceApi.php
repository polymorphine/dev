<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;


interface PhpDocRequiredForInterfaceApi
{
    /**
     * Whatever - no content check
     */
    public function interfaceMethodA(int $value): bool;
    public function interfaceMethodB(array $test): self;
}
