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
use Polymorphine\Dev\Fixer\NamedConstructorsFirstStaticFixer;


class NamedConstructorsFirstStaticFixerTest extends FixerTest
{
    public function testStaticConstructorsAreMovedToBeFirstStaticMethods()
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
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            class ExampleClass extends BaseExample implements ExampleInterface
            {
                private $self;
            
                /** someMethod phpDoc */
                public function someMethod()
                {
                    //code...
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
            
                public static function notConstructor(): SomeType
                {
                    //code without 'self' return type
                }
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testOnlyConstructorMethodsAreMoved()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass
            {
                /** Static Constructor */
                public static function constructor(ExampleClass $self): self
                {
                    $this->self = $self;
                }
            
                /** someMethod phpDoc */
                public function someMethod()
                {
                    //code...
                }
            
                public static function doSomething(): SomeType
                {
                }
            
                public static function fromData(array $data): self
                {
                    //return new self()
                }
            
                public static function notConstructor(): SomeType
                {
                    //code without 'self' return type
                }
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            class ExampleClass
            {
                /** Static Constructor */
                public static function constructor(ExampleClass $self): self
                {
                    $this->self = $self;
                }
            
                public static function fromData(array $data): self
                {
                    //return new self()
                }
            
                /** someMethod phpDoc */
                public function someMethod()
                {
                    //code...
                }
            
                public static function doSomething(): SomeType
                {
                }
            
                public static function notConstructor(): SomeType
                {
                    //code without 'self' return type
                }
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    protected function fixer(): NamedConstructorsFirstStaticFixer
    {
        return new NamedConstructorsFirstStaticFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/named_constructors_first_static', 'priority' => -40];
    }
}
