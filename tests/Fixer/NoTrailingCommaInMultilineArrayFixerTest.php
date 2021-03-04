<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Fixer;

use Polymorphine\Dev\Tests\FixerTest;
use Polymorphine\Dev\Fixer\NoTrailingCommaInMultilineArrayFixer;


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
            
            $oldArray = array(
                'one' => 'last',
            );
            
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
