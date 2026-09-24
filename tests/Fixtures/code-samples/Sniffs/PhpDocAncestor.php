<?php

namespace Polymorphine\Dev\CodeSamples\Sniffs;

// This class has invalid syntax on purpose - should still be able to parse methods without error
class PhpDocAncestor
{
    public function ancestorMethod(): int
    {
        return 42;
    }
