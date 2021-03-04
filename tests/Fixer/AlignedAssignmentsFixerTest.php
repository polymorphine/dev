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
use Polymorphine\Dev\Fixer\AlignedAssignmentsFixer;
use PhpCsFixer\Fixer\FixerInterface;


class AlignedAssignmentsFixerTest extends FixerTest
{
    public function testVariableAssignmentsAreAligned()
    {
        $code = <<<'CODE'
            <?php
            
            $x = 10;
            $bool = true;
            $another = 'string';
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            $x       = 10;
            $bool    = true;
            $another = 'string';
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMixedKindVariableAssignmentsAreAlignedSeparately()
    {
        $code = <<<'CODE'
            <?php
            
            $x = 10;
            $bool = true;
            $array['key_assoc'] = true;
            $this->thing = 'string';
            $this->foo = 'bar';
            SomeClass::$var = true;
            Another::$foo = 'bar';
            $array['key_assoc'] = true;
            $arrayWithKey['another'] = 'aligned';
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            $x    = 10;
            $bool = true;
            $array['key_assoc'] = true;
            $this->thing = 'string';
            $this->foo   = 'bar';
            SomeClass::$var = true;
            Another::$foo   = 'bar';
            $array['key_assoc']      = true;
            $arrayWithKey['another'] = 'aligned';
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMultilineAssignmentIsNotAligned()
    {
        $code = <<<'CODE'
            <?php
            
            $x = 10;
            $func = function (int $val) {
                return $val + 1;
            };
            $another = 'string';
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    protected function fixer(): FixerInterface
    {
        return new AlignedAssignmentsFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/aligned_assignments', 'priority' => -40];
    }
}
