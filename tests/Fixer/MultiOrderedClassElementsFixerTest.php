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

use PhpCsFixer\Fixer\FixerInterface;
use Polymorphine\Dev\Tests\FixerTest;
use Polymorphine\Dev\Fixer\MultiOrderedClassElementsFixer;


class MultiOrderedClassElementsFixerTest extends FixerTest
{
    public function testClassWithoutTestNameIsOrderedWithSrcConfig()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClass extends BaseExample implements ExampleInterface
            {
                private $self;
            
                /** someMethod phpDoc */
                public function someMethod() {}
            
                public static function notConstructor(): SomeType
                {
                }
            
                final public static function staticConstructor(array $data): BaseExample
                {
                }
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self
                {
                }
            
                public static function staticInterfaceConstructor(array $data): ExampleInterface
                {
                }
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            class ExampleClass extends BaseExample implements ExampleInterface
            {
            
                public static function notConstructor(): SomeType
                {
                }
            
                final public static function staticConstructor(array $data): BaseExample
                {
                }
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self
                {
                }
            
                public static function staticInterfaceConstructor(array $data): ExampleInterface
                {
                }
                private $self;
            
                /** someMethod phpDoc */
                public function someMethod() {}
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testClassWithTestNameIsOrderedWithTestConfig()
    {
        $code = <<<'CODE'
            <?php
            
            class ExampleClassTest extends BaseExample implements ExampleInterface
            {
                public static function notConstructor(): SomeType {}
            
                private $self;
            
                /** someMethod phpDoc */
                public function someMethod() {}
            
                private function getTestedClass(): ExampleClass {}
            
                public static function tearDownAfterClass(): void {}
            
                protected function tearDown(): void {}
            
                private static function assertSomething(): void {}
            
                protected static function staticConstructor(array $data): BaseExample {}
            
                public static function setUpBeforeClass(): void {}
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self {}
            
                public function test_Instantiation() {}
            
                public static function staticInterfaceConstructor(array $data): ExampleInterface {}
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            class ExampleClassTest extends BaseExample implements ExampleInterface
            {
            
                private $self;
            
                public static function setUpBeforeClass(): void {}
            
                public static function tearDownAfterClass(): void {}
            
                protected function tearDown(): void {}
            
                /** someMethod phpDoc */
                public function someMethod() {}
            
                public function test_Instantiation() {}
                public static function notConstructor(): SomeType {}
            
                /**
                 * Static constructor with phpDoc
                 */
                public static function fromData(array $data): self {}
            
                public static function staticInterfaceConstructor(array $data): ExampleInterface {}
            
                protected static function staticConstructor(array $data): BaseExample {}
            
                private function getTestedClass(): ExampleClass {}
            
                private static function assertSomething(): void {}
            }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    protected function fixer(): FixerInterface
    {
        $srcOrder = [
            'use_trait', 'case', 'constant_public', 'constant_protected', 'constant_private',
            'property_public_static', 'property_protected_static', 'property_private_static',
            'method_public_static', 'method_protected_static', 'method_private_static',
            'property_public', 'property_protected', 'property_private',
            'construct', 'magic', 'method_public', 'destruct', 'method_protected', 'method_private'
        ];

        $testOrder = [
            'use_trait', 'constant_public', 'constant_protected', 'constant_private',
            'property_public_static', 'property_protected_static', 'property_private_static',
            'property_public', 'property_protected', 'property_private',
            'construct', 'phpunit', 'magic', 'destruct',
            'method_public', 'method_public_static',
            'method_protected', 'method_protected_static',
            'method_private', 'method_private_static'
        ];

        return new MultiOrderedClassElementsFixer(dirname(__DIR__, 2), $srcOrder, $testOrder);
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/multi_ordered_class_elements', 'priority' => 65];
    }
}
