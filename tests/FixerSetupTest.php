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
        $this->assertInstanceOf(ConfigInterface::class, FixerSetup::config(dirname(__DIR__)));
    }

    public function test_ConfigFinder_IgnoresTestCodeSamples()
    {
        FixerSetup::usingTempPath('');
        $finder = FixerSetup::config(dirname(__DIR__))->getFinder();

        $ignoredFiles = $this->packagePath('tests/Fixtures/code-samples/');
        foreach ($finder as $file) {
            $this->assertFalse(strpos($file->getPathname(), $ignoredFiles));
        }
    }

    public function test_ConfigFinder_ForTempFile_IgnoresTestCodeSamples()
    {
        $tempPath = 'tests/Fixtures/PHP CS Fixertemp_folder6/';
        FixerSetup::usingTempPath($this->packagePath($tempPath . 'SomePath/AnyFile.php'));
        $finder = FixerSetup::config(dirname(__DIR__))->getFinder();

        $ignoredFile  = $this->packagePath($tempPath . 'tests/code-samples/IgnoredFile.php');
        $acceptedFile = $this->packagePath($tempPath . 'tests/UsedFile.php');
        foreach ($finder as $file) {
            $acceptedFile = $file->getPathname() === $acceptedFile ? 'FOUND' : $acceptedFile;
            $ignoredFile  = $file->getPathname() === $ignoredFile ? 'FOUND' : $ignoredFile;
        }
        $this->assertSame('FOUND', $acceptedFile);
        $this->assertNotSame('FOUND', $ignoredFile);
    }

    public function test_HeaderMetaData_IsReachedFromRootDirectory()
    {
        $expectedHeader = <<<'HEADER'
            This file is part of Polymorphine/Dev package.

            (c) Shudd3r <q3.shudder@gmail.com>

            This source file is subject to the MIT license that is bundled
            with this source code in the file LICENSE.
            HEADER;

        $rules = FixerSetup::config(dirname(__DIR__))->getRules();
        $this->assertSame($expectedHeader, $rules['header_comment']['header']);

        $rules = FixerSetup::config(__DIR__)->getRules();
        $this->assertFalse($rules['header_comment']);
    }

    private function packagePath(string $relativePath): string
    {
        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR));
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;
    }
}
