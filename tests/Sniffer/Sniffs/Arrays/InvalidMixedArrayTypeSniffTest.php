<?php

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Sniffer\Sniffs\Arrays;

use Polymorphine\Dev\Tests\SnifferTest;
use Polymorphine\Dev\Sniffer\Sniffs\Arrays\AmbiguousAssociativitySniff;


class InvalidMixedArrayTypeSniffTest extends SnifferTest
{
    public function testAssocArrayWithNonAssocValuesGivesWarning()
    {
        $this->assertWarningLines('./tests/CodeSamples/Sniffs/InvalidArrays.php', [5, 20, 22, 27]);
    }

    protected function sniffer(): string
    {
        return AmbiguousAssociativitySniff::class;
    }
}
