<?php

namespace Polymorphine\CodeStandards\Tests\Files\Sniffs;


interface PhpDocRequiredForInterfaceApi
{
    /**
     * Whatever - no content check
     */
    public function methodA(int $value): bool;

    public function methodB(array $test): self;
}
