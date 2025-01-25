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
    public function test_SingleLineChainCalls_AreNotChanged()
    {
        $code = <<<'CODE'
            <?php
            
            $someVar = $callable()->withSomething('string')->build();
            return $this->value->methodA()->methodB($foo === $bar)->baz($someVar);

            CODE;

        $this->assertUnchanged($code);
    }

    public function test_LineBreakChainCalls_AreExpandedAndAligned()
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

        $this->assertFixed($code, $expected);
    }

    public function test_NestedMultilineChains_AreAligned()
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

        $this->assertFixed($code, $expected);
    }

    public function test_CodeWithoutObjectOperator_IsSkipped()
    {
        $code = <<<'CODE'
            <?php
            
            $someVar = function_call();
            
            CODE;

        $this->assertUnchanged($code);
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
