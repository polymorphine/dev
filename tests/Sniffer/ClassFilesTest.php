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
use Polymorphine\Dev\Sniffer\ClassFiles;
use Polymorphine\Dev\CodeSamples\Sniffs\PhpDocClass;
use Polymorphine\Dev\CodeSamples\Sniffs\PhpDocParent;
use Polymorphine\Dev\CodeSamples\Sniffs\PhpDocInvalidClass;


class ClassFilesTest extends TestCase
{
    /** @dataProvider classNames */
    public function test_ExistingClassName_ReturnsClassDefinitionFilename(string $className, string $expectedFilename)
    {
        $classes = new ClassFiles();
        $this->assertSame($this->path($expectedFilename), $classes->filename($className));
    }

    public function test_NotExistingClassName_ReturnsNull()
    {
        $classes = new ClassFiles();
        $this->assertNull($classes->filename('Polymorphine\Dev\NotExistingClass'));
    }

    public function test_AlreadyLoadedClassName_ReturnsClassDefinitionFilename()
    {
        $classes = new ClassFiles();
        $this->assertSame(__FILE__, $classes->filename(self::class));
    }

    public static function classNames(): iterable
    {
        return [
            [PhpDocClass::class, 'Fixtures/code-samples/Sniffs/PhpDocClass.php'],
            [PhpDocParent::class, 'Fixtures/code-samples/Sniffs/PhpDocParent.php'],
            [PhpDocInvalidClass::class, 'Fixtures/code-samples/Sniffs/PhpDocInvalidClass.php']
        ];
    }

    private function path(string $name): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $name);
    }
}
