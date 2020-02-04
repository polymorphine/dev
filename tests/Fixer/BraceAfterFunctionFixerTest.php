<?php

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
use Polymorphine\Dev\Fixer\BraceAfterFunctionFixer;


class BraceAfterFunctionFixerTest extends FixerTest
{
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
                public function withSomething(string $value): Type
                {
                    return $this->value->methodA();
                }
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            class Test {
                public function withSomething(string $value): Type {
                    return $this->value->methodA();
                }
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testUnusualMethodFormattingIsFixed()
    {
        $code = <<<'CODE'
            <?php
            
            class Test {
                public function withSomething(string $value){
                    return $this->value->methodA();
                }
            
                public function GetType(string $value):Type
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
            
                public function GetType(string $value):Type {
                    return $this->value->methodA();
                }
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    protected function fixer(): BraceAfterFunctionFixer
    {
        return new BraceAfterFunctionFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/brace_after_method', 'priority' => -40];
    }
}
