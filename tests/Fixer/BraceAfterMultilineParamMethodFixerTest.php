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
use Polymorphine\Dev\Fixer\BraceAfterMultilineParamMethodFixer;


class BraceAfterMultilineParamMethodFixerTest extends FixerTest
{
    public function testSingleLineDefinition_BraceFromNextLine_IsNotChanged()
    {
        $code = $this->wrap(<<<'CODE'
            
                public function withSomething(string $value, array $another): Type
                {
                    return $this->value->methodA();
                }
            
            CODE);

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testMultilineDefinition_BraceFromNextLine_IsFixed()
    {
        $code = $this->wrap(<<<'CODE'
            
                public function withSomething(
                    string $value,
                    array $another
                ): Type
                {
                    return $this->value->methodA();
                }
            
            CODE, 'trait');

        $expected = $this->wrap(<<<'CODE'
            
                public function withSomething(
                    string $value,
                    array $another
                ): Type {
                    return $this->value->methodA();
                }
            
            CODE, 'trait');

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMultilineDefinition_MissingWhitespace_IsFixed()
    {
        $code = $this->wrap(<<<'CODE'
            
                public function withSomething(
                    string $value,
                    array $another
                ){
                    return $this->value->methodA();
                }
            
            CODE);

        $expected = $this->wrap(<<<'CODE'
            
                public function withSomething(
                    string $value,
                    array $another
                ) {
                    return $this->value->methodA();
                }
            
            CODE);

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testAbstractMultilineDefinition_DoesNotAffectNextMethod()
    {
        $code = $this->wrap(<<<'CODE'

                abstract public function isValid(
                    int $value,
                    array $nextValue
                );
                
                public function withSomething(string $value): Type
                {
                    return $this->value->methodA();
                }
            
            CODE);

        $this->assertSame($code, $this->runner->fix($code));
    }

    protected function fixer(): BraceAfterMultilineParamMethodFixer
    {
        return new BraceAfterMultilineParamMethodFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/brace_after_multiline_param_method', 'priority' => -40];
    }

    private function wrap(string $code, string $type = 'class'): string
    {
        return <<<'CODE'
            <?php
            
            CODE . $type . ' Test' . <<<'CODE'
            
            {
                public int $property = 20;
                
            CODE . $code . <<<'CODE'
            }
            
            CODE;
    }
}
