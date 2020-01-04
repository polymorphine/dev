<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests\Fixer;

use Polymorphine\CodeStandards\Tests\FixerTest;
use Polymorphine\CodeStandards\Fixer\NoTrailingCommaInMultilineArrayFixer;


class NoTrailingCommaInMultilineArrayFixerTest extends FixerTest
{
    public function testTrailingCommaIsRemovedFromMultilineArray()
    {
        $code = <<<'CODE'
            <?php
            
            $array = [
                'one' => 1,
                'two' => 2,
            ];

            CODE;

        $expected = <<<'CODE'
            <?php
            
            $array = [
                'one' => 1,
                'two' => 2
            ];

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    protected function fixer(): NoTrailingCommaInMultilineArrayFixer
    {
        return new NoTrailingCommaInMultilineArrayFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/no_trailing_comma_after_multiline_array', 'priority' => -40];
    }
}
