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
use Polymorphine\CodeStandards\Fixer\ConstructorsFirstFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class ConstructorsFirstFixerTest extends TestCase
{
    private TestRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new ConstructorsFirstFixer()]);
    }

    public function testConstructorsAreMovedToTop()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass
            {
                private $self;
            
                public function someMethod()
                {
                    //code...
                }
            
                public static function fromData(array $data): self
                {
                    //return new self()
                }
            
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            }

            CODE;

        $expected = <<<'CODE'
            <?php
            
            class ExampleClass
            {
                private $self;
            
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            public static function fromData(array $data): self
                {
                    //return new self()
                }
            
                public function someMethod()
                {
                    //code...
                }
            
                }

            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }
}
