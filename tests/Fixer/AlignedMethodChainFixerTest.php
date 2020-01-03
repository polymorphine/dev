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
use Polymorphine\CodeStandards\Fixer\AlignedMethodChainFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class AlignedMethodChainFixerTest extends TestCase
{
    private TestRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new AlignedMethodChainFixer()]);
    }

    public function testSingleLineChainCallsAreNotChanged()
    {
        $code = <<<'CODE'
            <?php
            
            $someVar = $callable()->withSomething('string')->build();
            return $this->value->methodA()->methodB($foo === $bar)->baz($someVar);

            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testLineBreakChainCallsAreExpandedAndAligned()
    {
        $code = <<<'CODE'
            <?php
            
            $someVar = $callable()->withSomething('string')
                ->build();
            return $this->value->methodA()
                ->methodB($foo === $bar)->baz($someVar);

            CODE;

        $expected = <<<'CODE'
            <?php
            
            $someVar = $callable()->withSomething('string')
                                  ->build();
            return $this->value->methodA()
                               ->methodB($foo === $bar)
                               ->baz($someVar);

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }
}
