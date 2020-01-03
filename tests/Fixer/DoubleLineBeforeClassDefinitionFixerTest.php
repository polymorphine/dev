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
use Polymorphine\CodeStandards\Fixer\DoubleLineBeforeClassDefinitionFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class DoubleLineBeforeClassDefinitionFixerTest extends TestCase
{
    private TestRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new DoubleLineBeforeClassDefinitionFixer()]);
    }

    public function testTwoEmptyLinesAreInsertedBeforeClassDefinition()
    {
        $code = <<<'CODE'
            <?php
            
            namespace Some\Package;
            class ExampleClass
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
            
            
            class ExampleClass
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
}
