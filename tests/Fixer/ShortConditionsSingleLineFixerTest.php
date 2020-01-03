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
use Polymorphine\CodeStandards\Fixer\ShortConditionsSingleLineFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class ShortConditionsSingleLineFixerTest extends TestCase
{
    private TestRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new ShortConditionsSingleLineFixer()]);
    }

    public function testShortConditionsAreTurnedIntoSingleLine()
    {
        $code = <<<'CODE'
            <?php
            
            if ($variable !== CONSTANT_VALUE) {
                return;
            }

            CODE;

        $expected = <<<'CODE'
            <?php
            
            if ($variable !== CONSTANT_VALUE) { return; }

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testLongConditionsAreNotChanged()
    {
        $code = <<<'CODE'
            <?php
            
            if ($variable !== CONSTANT_VALUE) {
                return $expression + $secondExpression;
            }

            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }
}
