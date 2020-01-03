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
use Polymorphine\CodeStandards\Fixer\BraceAfterFunctionFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class BraceAfterFunctionFixerTest extends TestCase
{
    private TestRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new BraceAfterFunctionFixer()]);
    }

    public function testFunctionBracesFromNextLineAreMoved()
    {
        $code = <<<'CODE'
            <?php
            
            function withSomething(Test $value)
            {
                return $value->methodA();
            }

            CODE;

        $expected = <<<'CODE'
            <?php
            
            function withSomething(Test $value) {
                return $value->methodA();
            }

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMethodBracesFromNextLineAreMoved()
    {
        $code = <<<'CODE'
            <?php
            
            class Test {
                public function withSomething(string $value)
                {
                    return $this->value->methodA();
                }
            }

            CODE;

        $expected = <<<'CODE'
            <?php
            
            class Test {
                public function withSomething(string $value) {
                    return $this->value->methodA();
                }
            }

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }
}
