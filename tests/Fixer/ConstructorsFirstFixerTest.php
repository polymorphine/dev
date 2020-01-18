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
use Polymorphine\CodeStandards\Fixer\ConstructorsFirstFixer;


class ConstructorsFirstFixerTest extends FixerTest
{
    public function testConstructorsAreMovedToTop()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass extends BaseExample implements ExampleInterface
            {
                private $self;
            
                /** someMethod phpDoc */
                public function someMethod()
                {
                    //code...
                }
            
                public static function notConstructor(): SomeType
                {
                    //code without 'self' return type
                }
            
                final public static function staticConstructor(array $data): BaseExample
                {
                    //return new self()
                }
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self
                {
                    //return new self()
                }
            
                public static function staticInterfaceConstructor(array $data): ExampleInterface
                {
                    //return new self()
                }
            
                /** Main Constructor */
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            class ExampleClass extends BaseExample implements ExampleInterface
            {
                private $self;
            
                /** Main Constructor */
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            
                final public static function staticConstructor(array $data): BaseExample
                {
                    //return new self()
                }
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self
                {
                    //return new self()
                }
            
                public static function staticInterfaceConstructor(array $data): ExampleInterface
                {
                    //return new self()
                }
            
                /** someMethod phpDoc */
                public function someMethod()
                {
                    //code...
                }
            
                public static function notConstructor(): SomeType
                {
                    //code without 'self' return type
                }
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMainConstructorIsMovedToTop()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass implements ExampleInterface
            {
                /** someMethod phpDoc */
                public static function someMethod(): ExampleInterface
                {
                    //code...
                }
            
                final public static function staticConstructor(array $data): self
                {
                    //return new self()
                }
            
                /** Main Constructor */
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self
                {
                    //return new self()
                }
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            class ExampleClass implements ExampleInterface
            {
                /** Main Constructor */
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            
                /** someMethod phpDoc */
                public static function someMethod(): ExampleInterface
                {
                    //code...
                }
            
                final public static function staticConstructor(array $data): self
                {
                    //return new self()
                }
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self
                {
                    //return new self()
                }
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testFirstMainConstructorIsNotMoved()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass
            {
                /** Main Constructor */
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            }
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testFirstStaticConstructorIsNotMoved()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass
            {
                private $self;
            
                /** Static Constructor */
                public static function constructor(ExampleClass $self): self
                {
                    $this->self = $self;
                }
            }
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    protected function fixer(): ConstructorsFirstFixer
    {
        return new ConstructorsFirstFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/constructors_first', 'priority' => -40];
    }
}
