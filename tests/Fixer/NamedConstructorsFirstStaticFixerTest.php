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
    public function test_StaticConstructors_AreMovedToBeFirstStaticMethods()
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

        $this->assertFixed($code, $expected);
    }

    public function test_OnlyConstructorMethods_AreMoved()
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

        $this->assertFixed($code, $expected);
    }

    public function test_AnonymousFunctions_AreIgnored()
    {
        $code = <<<'CODE'
            <?php declare(strict_types=1);
            
            class Stream implements StreamInterface
            {
                private $resource;
            
                public function __construct($resource)
                {
                    $this->resource = $resource;
                }
            
                public static function fromResourceUri(string $streamUri, $mode = 'r'): self
                {
                    set_error_handler(function () use ($mode) {
                        throw new InvalidArgumentException('Invalid stream resource mode');
                    }, E_WARNING);
                    $resource = fopen($streamUri, $mode);
                    restore_error_handler();
            
                    return new self($resource);
                }
            
                public static function fromBodyString(string $body): self {}
            
                public function doSomething(): string {}
            }
            
            CODE;

        $this->assertUnchanged($code);
    }

    public function test_MultipleClassesInSingleFile_AreReordered()
    {
        $code = <<<'CODE'
            <?php
            class ExampleClass
            {
                public static function someMethod(): void {}
                public static function instanceExample(): self {}
            }
            
            class AnotherClass
            {
                public static function someMethod(): void {}
                public static function instanceAnother(): self {}
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            class ExampleClass
            {
                public static function instanceExample(): self {}
                public static function someMethod(): void {}
            }
            
            class AnotherClass
            {
                public static function instanceAnother(): self {}
                public static function someMethod(): void {}
            }
            
            CODE;

        $this->assertFixed($code, $expected);
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
