<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests;

use PHPUnit\Framework\TestCase;
use Polymorphine\Dev\FixerFactory;
use PhpCsFixer\ConfigInterface;


class FixerFactoryTest extends TestCase
{
    public function testConfigInstantiation()
    {
        $this->assertInstanceOf(ConfigInterface::class, FixerFactory::createFor(__FILE__));
    }

    public function testConfigFinder_IgnoresCodeSamples()
    {
        $finder = FixerFactory::createFor($this->packagePath('cs-fixer.php.dist'))->getFinder();

        $this->assertTrue($finder->hasResults());

        $excludedPath = $this->packagePath('tests/Fixtures/code-samples');
        foreach ($finder->getIterator() as $file) {
            $this->assertStringNotContainsString($excludedPath, $file->getPath());
        }
    }

    public function testHeaderIsReadFromLaunchFile()
    {
        $expectedHeader = <<<'HEADER'
            This file is part of Polymorphine/Dev package.

            (c) Shudd3r <q3.shudder@gmail.com>

            This source file is subject to the MIT license that is bundled
            with this source code in the file LICENSE.
            HEADER;

        $rules = FixerFactory::createFor($this->packagePath('cs-fixer.php.dist'))->getRules();
        $this->assertSame($expectedHeader, $rules['header_comment']['header']);

        $file  = $this->packagePath('tests/Fixtures/code-samples/Fixer/given-global.php');
        $rules = FixerFactory::createFor($file)->getRules();
        $this->assertSame('LOL surprise comment!', $rules['header_comment']['header']);

        $file  = $this->packagePath('tests/Fixtures/code-samples/Fixer/given-ExampleClass.php');
        $rules = FixerFactory::createFor($file)->getRules();
        $this->assertFalse($rules['header_comment']);
    }

    private function packagePath(string $relativePath): string
    {
        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR));
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;
    }
}
