<?php

namespace Polymorphine\CodeStandards\Tests\Files\Sniffs;


interface PhpDocRequiredForInterfaceApi
{
    /**
     * Whatever - no content check
     */
    public function interfaceMethodA(int $value): bool;
    public function interfaceMethodB(array $test): self;
}
