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

use Polymorphine\CodeStandards\Fixer\BraceAfterFunctionFixer;
use PHPUnit\Framework\TestCase;


class BraceAfterFunctionFixerTest extends TestCase
{
    use FixerTestMethods;

    protected function setUp(): void
    {
        $this->setRunner(new BraceAfterFunctionFixer());
    }

    public function testFunctionBracesFromNextLineAreMoved()
    {
        $code = $this->code(<<<'PHP'
            
            function withSomething(Test $value)
            {
                return $value->methodA();
            }

            PHP);

        $expected = $this->code(<<<'PHP'
            
            function withSomething(Test $value) {
                return $value->methodA();
            }

            PHP);

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMethodBracesFromNextLineAreMoved()
    {
        $code = $this->code(<<<'PHP'
            
            class Test {
                public function withSomething(string $value)
                {
                    return $this->value->methodA();
                }
            }

            PHP);

        $expected = $this->code(<<<'PHP'
            
            class Test {
                public function withSomething(string $value) {
                    return $this->value->methodA();
                }
            }

            PHP);

        $this->assertSame($expected, $this->runner->fix($code));
    }
}
