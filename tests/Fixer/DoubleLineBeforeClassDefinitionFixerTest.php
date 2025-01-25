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
use Polymorphine\Dev\Fixer\DoubleLineBeforeClassDefinitionFixer;


class DoubleLineBeforeClassDefinitionFixerTest extends FixerTest
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

    protected function fixer(): DoubleLineBeforeClassDefinitionFixer
    {
        return new DoubleLineBeforeClassDefinitionFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/double_line_before_class_definition', 'priority' => -40];
    }
}
