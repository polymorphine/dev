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

use PHPUnit\Framework\TestCase;
use Polymorphine\CodeStandards\Fixer\NoTrailingCommaInMultilineArrayFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class NoTrailingCommaInMultilineArrayFixerTest extends TestCase
{
    private TestRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new NoTrailingCommaInMultilineArrayFixer()]);
    }

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
}
