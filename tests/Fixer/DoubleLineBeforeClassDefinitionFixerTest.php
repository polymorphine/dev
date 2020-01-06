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

use Polymorphine\CodeStandards\Tests\FixerTest;
use Polymorphine\CodeStandards\Fixer\DoubleLineBeforeClassDefinitionFixer;


class DoubleLineBeforeClassDefinitionFixerTest extends FixerTest
{
    public function testWhitespaceIsExpandedIntoTwoEmptyLinesBeforeClassDefinition()
    {
        $code = <<<'CODE'
            <?php
            
            namespace Some\Package;
            /**
             * class description
             */
            
            final class ExampleClass
            {
                private $self;
            
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
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
                private $self;
            
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            }

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testTwoEmptyLinesAreInsertedBeforeClassDefinition()
    {
        $code = <<<'CODE'
            <?php
            
            namespace Some\Package;//comment
            interface ExampleInterface
            {
                private function doSomething(): void;
            }

            CODE;

        $expected = <<<'CODE'
            <?php
            
            namespace Some\Package;
            
            
            //comment
            interface ExampleInterface
            {
                private function doSomething(): void;
            }

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    protected function fixer(): DoubleLineBeforeClassDefinitionFixer
    {
        return new DoubleLineBeforeClassDefinitionFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/double_line_before_class_definition', 'priority' => -40];
    }
}
