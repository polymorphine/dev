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
use Polymorphine\Dev\Fixer\AlignedMethodChainFixer;


class AlignedMethodChainFixerTest extends FixerTest
{
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

    public function testNestedMultilineChainsAreAligned()
    {
        $code = <<<'CODE'
            <?php
            
            $call->withSomething(function () {
                $this->doSomething()
                ->andMore();
            })
            ->build();
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            $call->withSomething(function () {
                     $this->doSomething()
                          ->andMore();
                 })
                 ->build();
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testCodeWithoutObjectOperatorIsSkipped()
    {
        $code = <<<'CODE'
            <?php
            
            $someVar = function_call();
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    protected function fixer(): AlignedMethodChainFixer
    {
        return new AlignedMethodChainFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/aligned_method_chain', 'priority' => -40];
    }
}
