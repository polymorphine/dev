<?php

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
use Polymorphine\Dev\Fixer\AlignedTypedPropertiesFixer;
use PhpCsFixer\Fixer\FixerInterface;


class AlignedTypedPropertiesFixerTest extends FixerTest
{
    public function testPropertiesAreAligned()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass
            {
                private Foo $foo;
                private Typed $bar;
                public Typed $variable;
                protected static TypedProperty $x;
                protected static ShortType $longVariable;
                private SomeInterface $baz;
            
                protected static Foo $var1;
                protected static ?FooX $var2;
            
                public callable $test;
                public ?object $object;
                public SomeTypeLongest $varTyped;
                public int $number    = 1000;
                public array $another = ['a', 'b'];
            
                /** Main Constructor */
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
                private Foo   $foo;
                private Typed $bar;
                public Typed $variable;
                protected static TypedProperty $x;
                protected static ShortType     $longVariable;
                private SomeInterface $baz;
            
                protected static Foo   $var1;
                protected static ?FooX $var2;
            
                public callable        $test;
                public ?object         $object;
                public SomeTypeLongest $varTyped;
                public int             $number    = 1000;
                public array           $another = ['a', 'b'];
            
                /** Main Constructor */
                public function __construct(ExampleClass $self)
                {
                    $this->self = $self;
                }
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    protected function fixer(): FixerInterface
    {
        return new AlignedTypedPropertiesFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/aligned_properties', 'priority' => -39];
    }
}
