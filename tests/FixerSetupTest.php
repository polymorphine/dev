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
use Polymorphine\Dev\FixerSetup;
use PhpCsFixer\ConfigInterface;


class FixerSetupTest extends TestCase
{
    public function test_ConfigInstantiation()
    {
        $this->assertInstanceOf(ConfigInterface::class, FixerSetup::createFor(__FILE__));
    }

    public function test_ConfigFinder_IgnoresCodeSamples()
    {
        FixerSetup::usingTempPath('');
        $finder = FixerSetup::createFor($this->packagePath('cs-fixer.php.dist'))->getFinder();

        $ignoredFiles = $this->packagePath('tests/Fixtures/code-samples/');
        foreach ($finder as $file) {
            $this->assertFalse(strpos($file->getPathname(), $ignoredFiles));
        }
    }

    public function test_ConfigFinder_ForTempFile_IgnoresCodeSamples()
    {
        $tempPath = 'tests/Fixtures/PHP CS Fixertemp_folder6/';
        FixerSetup::usingTempPath($this->packagePath($tempPath . 'SomePath/AnyFile.php'));
        $finder = FixerSetup::createFor($this->packagePath('cs-fixer.php.dist'))->getFinder();

        $ignoredFile  = $this->packagePath($tempPath . 'tests/code-samples/IgnoredTmpFile.php');
        $acceptedFile = $this->packagePath($tempPath . 'tests/FixedTmpFile.php');
        foreach ($finder as $file) {
            $acceptedFile = $file->getPathname() === $acceptedFile ? 'FOUND' : $acceptedFile;
            $ignoredFile  = $file->getPathname() === $ignoredFile ? 'FOUND' : $ignoredFile;
        }
        $this->assertSame('FOUND', $acceptedFile);
        $this->assertNotSame('FOUND', $ignoredFile);
    }

    public function test_Header_IsReadFromLaunchFile()
    {
        $expectedHeader = <<<'HEADER'
            This file is part of Polymorphine/Dev package.

            (c) Shudd3r <q3.shudder@gmail.com>

            This source file is subject to the MIT license that is bundled
            with this source code in the file LICENSE.
            HEADER;

        $rules = FixerSetup::createFor($this->packagePath('cs-fixer.php.dist'))->getRules();
        $this->assertSame($expectedHeader, $rules['header_comment']['header']);

        $file  = $this->packagePath('tests/Fixtures/code-samples/Fixer/given-global.php');
        $rules = FixerSetup::createFor($file)->getRules();
        $this->assertSame('LOL surprise comment!', $rules['header_comment']['header']);

        $file  = $this->packagePath('tests/Fixtures/code-samples/Fixer/given-ExampleClass.php');
        $rules = FixerSetup::createFor($file)->getRules();
        $this->assertFalse($rules['header_comment']);
    }

    private function packagePath(string $relativePath): string
    {
        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR));
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;
    }
}
