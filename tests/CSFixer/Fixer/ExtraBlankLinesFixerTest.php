<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\CSFixer\Fixer;

use Polymorphine\Dev\Tests\CSFixer\FixerTest;
use Polymorphine\Dev\CSFixer\Fixer\ExtraBlankLinesFixer;


class ExtraBlankLinesFixerTest extends FixerTest
{
    public function test_WhitespaceBeforeClassDefinition_IsExpandedIntoTwoEmptyLines()
    {
        $code = <<<'CODE'
            <?php
            
            namespace Some\Package;
            /**
             * class description
             */
            
            final class ExampleClass
            {
                private ExampleClass $self;
            
            
            
                private int $idx;
            
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
            
            
                    $this->idx = 100;
                }
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            namespace Some\Package;
            
            
            /**
             * class description
             */
            final class ExampleClass
            {
                private ExampleClass $self;
            
                private int $idx;
            
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
            
                    $this->idx = 100;
                }
            }
            
            CODE;

        $this->assertFixed($code, $expected);
    }

    public function test_TwoEmptyLinesBeforeClassDefinition_AreInserted()
    {
        $code = <<<'CODE'
            <?php
            
            
            namespace Some\Package; //comment
            interface ExampleInterface
            {
                private function doSomething(): void;
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            namespace Some\Package; //comment
            
            
            interface ExampleInterface
            {
                private function doSomething(): void;
            }
            
            CODE;

        $this->assertFixed($code, $expected);
    }

    protected function fixer(): ExtraBlankLinesFixer
    {
        return new ExtraBlankLinesFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/extra_blank_lines', 'priority' => -40];
    }
}
