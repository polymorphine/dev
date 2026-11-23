<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\CSFixer\Fixer;

use Polymorphine\Dev\Tests\CSFixer\FixerTest;
use Polymorphine\Dev\CSFixer\Fixer\NoCommaMultilineArrayFixer;


class NoCommaMultilineArrayFixerTest extends FixerTest
{
    public function test_TrailingComma_IsRemovedFromMultilineArray()
    {
        $code = <<<'CODE'
            <?php
            
            $array = [
                'one' => 1,
                'two' => 2,
            ];
            
            $oldArray = array(
                'one' => 'last',
            );
            
            return ['foo' => 1, 'bar' => [
                'nested',
                'multiline',
            ], 'baz' => 3];
            CODE;

        $expected = <<<'CODE'
            <?php
            
            $array = [
                'one' => 1,
                'two' => 2
            ];
            
            $oldArray = array(
                'one' => 'last'
            );
            
            return ['foo' => 1, 'bar' => [
                'nested',
                'multiline'
            ], 'baz' => 3];
            CODE;

        $this->assertFixed($code, $expected);
    }

    protected function fixer(): NoCommaMultilineArrayFixer
    {
        return new NoCommaMultilineArrayFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/no_comma_multiline_array', 'priority' => -40];
    }
}
