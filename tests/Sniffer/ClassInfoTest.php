<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Sniffer;

use PHPUnit\Framework\TestCase;
use Polymorphine\Dev\Sniffer\ClassInfo;
use Polymorphine\Dev\Sniffer\Tokens;
use Polymorphine\Dev\Tests\Fixtures\Tools\SnifferTokens;
use InvalidArgumentException;


class ClassInfoTest extends TestCase
{
    public function test_ClassInfo_ForNotClassTokens_CannotBeInstantiated()
    {
        $notClass = $this->classInfo(<<<'PHP'
            <?php
            namespace Foo\Names\Bar;
        PHP);
        $this->assertNull($notClass);
    }

    public function test_ClassInfoInstantiation_WithNonKeywordIndex_ThrowsException()
    {
        $tokens = new Tokens(SnifferTokens::fromCode(<<<'PHP'
            <?php
            namespace Foo\Names\Bar;
            class MyClass {}
        PHP));
        $this->expectException(InvalidArgumentException::class);
        new ClassInfo($tokens, 5);
    }

    public function test_ApiMethods_ReturnsListOfPublicMethods()
    {
        $file     = dirname(__DIR__) . '/Fixtures/code-samples/Fixer/expected-ClassOrder.php';
        $class    = $this->classInfo(file_get_contents($file));
        $expected = ['instance', 'doPublicStatic', '__construct', 'setUpBeforeClass', 'doPublic'];
        $this->assertSame($expected, $class->apiMethods());
    }

    public function test_ParentName_ForNotInheritedCode_ReturnsEmptyString()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php
            namespace Foo\Names\Bar;
            class MyClass implements SomeInterface {}
        PHP);
        $this->assertEmpty($class->parentName());
    }

    public function test_ParentName_ForNotImplementingCode_ReturnsEmptyArray()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php
            namespace Foo\Names\Bar;
            class MyClass extends SomeParent {}
        PHP);
        $this->assertEmpty($class->interfaces());
    }

    public function test_GlobalNamesResolution()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php
            
            class MyClass extends SomeParent implements InterfaceOne, \InterfaceTwo {}
        PHP);
        $this->assertSame('SomeParent', $class->parentName());
        $this->assertSame(['InterfaceOne', 'InterfaceTwo'], $class->interfaces());

        $class = $this->classInfo(<<<'PHP'
            <?php
            namespace Foo\Names\Bar;
            class MyClass extends \SomeParent implements \InterfaceOne, \InterfaceTwo {}
        PHP);
        $this->assertSame('SomeParent', $class->parentName());
        $this->assertSame(['InterfaceOne', 'InterfaceTwo'], $class->interfaces());
    }

    public function test_NamespaceNamesResolution()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php
            namespace Foo\Names\Bar;
            class MyClass extends SomeParent implements InterfaceOne, InterfaceTwo {}
        PHP);
        $this->assertSame('Foo\Names\Bar\SomeParent', $class->parentName());
        $this->assertSame(['Foo\Names\Bar\InterfaceOne', 'Foo\Names\Bar\InterfaceTwo'], $class->interfaces());
    }

    public function test_ImportedNamesResolution()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php
            namespace Foo\Names\Bar;
            use Vendor\Package;
            use Foo\Baz\InterfaceTwo;
            class MyClass extends Package\SomeParent implements Baz\InterfaceOne, InterfaceTwo {}
        PHP);
        $this->assertSame('Vendor\Package\SomeParent', $class->parentName());
        $this->assertSame(['Foo\Names\Bar\Baz\InterfaceOne', 'Foo\Baz\InterfaceTwo'], $class->interfaces());
    }

    public function test_ImportedAliasResolution()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php
            namespace Foo\Bar;
            use Vendor\PackageName as Package;
            use Foo\Baz\Contract as InterfaceTwo;
            class MyClass extends Package\SomeParent implements Baz\InterfaceOne, InterfaceTwo {}
        PHP);
        $this->assertSame('Vendor\PackageName\SomeParent', $class->parentName());
        $this->assertSame(['Foo\Bar\Baz\InterfaceOne', 'Foo\Baz\Contract'], $class->interfaces());
    }

    public function test_GrouppedImportConstructsNameResolution()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php declare(strict_types=1);
            
            namespace Fizz;
            
            use Vendor\PackageName\ {
                Foo as Package,
                Baz
            };
            use Foo\Baz\Contract as InterfaceTwo, Fizz\SomeType;
            
            
            class MyClass extends Package\SomeParent implements SomeType\Bazz, Baz, InterfaceTwo {}
        PHP);
        $this->assertSame('Vendor\PackageName\Foo\SomeParent', $class->parentName());
        $this->assertSame(['Fizz\SomeType\Bazz', 'Vendor\PackageName\Baz', 'Foo\Baz\Contract'], $class->interfaces());
    }

    public function test_ReadInheritedInterfaces()
    {
        $class = $this->classInfo(<<<'PHP'
            <?php declare(strict_types=1);
            
            namespace Fizz;
            
            use Vendor\Pack\ {
                Foo as Package,
                Baz
            };
            use Foo\Baz\Contract as InterfaceTwo, Fizz\SomeType;
            
            
            interface MyClass extends Package\SomeParent, Baz, InterfaceTwo {}
        PHP);

        $this->assertSame('', $class->parentName());
        $this->assertSame(['Vendor\Pack\Foo\SomeParent', 'Vendor\Pack\Baz', 'Foo\Baz\Contract'], $class->interfaces());
    }

    private function classInfo(string $content): ?ClassInfo
    {
        return ClassInfo::fromTokens(new Tokens(SnifferTokens::fromCode($content)));
    }
}
