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
use Polymorphine\Dev\Fixer\AlignedArrayValuesFixer;
use PhpCsFixer\Fixer\FixerInterface;


class AlignedArrayValuesFixerTest extends FixerTest
{
    public function testNonAssociativeArraysAreNotChanged()
    {
        $code = <<<'CODE'
            <?php
            
            $x = [
                'a', 'abc',
                'def'
            ];
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testSingleLineArraysAreNotChanged()
    {
        $code = <<<'CODE'
            <?php
            
            $x = ['a' => 10, 'abc' => 20];
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testMultilineArraysAreAligned()
    {
        $code = <<<'CODE'
            <?php
            
            $x = [
                'a' => 10,
                'abc' => ['foo' => $x, 'bar' => $y],
                'foo-bar' => 12,
                'baz' => ['one' => 1]
            ];
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            $x = [
                'a'       => 10,
                'abc'     => ['foo' => $x, 'bar' => $y],
                'foo-bar' => 12,
                'baz'     => ['one' => 1]
            ];
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testNotExclusivelyMultilineArraysAreNotChanged()
    {
        $code = <<<'CODE'
            <?php
            
            $x = [
                'a' => ['foo' => $x, 'bar' => $y],
                'abc' => $x, 'bar' => $y,
                'foo-bar' => 12,
                'baz' => ['one' => 1]
            ];
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    protected function fixer(): FixerInterface
    {
        return new AlignedArrayValuesFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/aligned_array_values', 'priority' => -40];
    }
}
