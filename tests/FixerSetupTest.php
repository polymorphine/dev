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
        $this->assertInstanceOf(ConfigInterface::class, FixerSetup::config());
    }

    public function test_ConfigFinder_IgnoresTestCodeSamples()
    {
        FixerSetup::init(dirname(__DIR__));

        $this->assertPathAccepted($this->packagePath('tests/Fixtures'));
        $this->assertPathIgnored($this->packagePath('tests/Fixtures/code-samples'));
    }

    public function test_ConfigFinder_ForTempFile_IgnoresTestCodeSamples()
    {
        $tempPath = 'tests/Fixtures/PHP CS Fixertemp_folder6/';
        FixerSetup::init($this->packagePath('src'), $this->packagePath($tempPath . 'SomePath/AnyFile.php'));

        $this->assertPathAccepted($this->packagePath($tempPath . 'tests/UsedFile.php'));
        $this->assertPathIgnored($this->packagePath($tempPath . 'tests/code-samples/IgnoredFile.php'));
    }

    public function test_BinaryFilesRegisteredOnInitialization_AreFixed()
    {
        $binFile = $this->packagePath('polymorphine-skeleton');

        FixerSetup::init(__DIR__);
        $this->assertPathIgnored($binFile);

        FixerSetup::init(dirname(__DIR__), $binFile);
        $this->assertPathAccepted($binFile);
    }

    public function test_HeaderMetaData_IsReachedFromRootDirectory()
    {
        $expectedHeader = <<<'HEADER'
            This file is part of Polymorphine/Dev package.

            (c) Shudd3r <q3.shudder@gmail.com>

            This source file is subject to the MIT license that is bundled
            with this source code in the file LICENSE.
            HEADER;

        FixerSetup::init(dirname(__DIR__));
        $rules = FixerSetup::config()->getRules();
        $this->assertSame($expectedHeader, $rules['header_comment']['header']);

        FixerSetup::init(__DIR__);
        $rules = FixerSetup::config()->getRules();
        $this->assertFalse($rules['header_comment']);
    }

    public function assertPathAccepted(string $acceptedPath): void
    {
        $this->assertTrue($this->isIteratedPath($acceptedPath));
    }

    public function assertPathIgnored(string $ignoredPath): void
    {
        $this->assertFalse($this->isIteratedPath($ignoredPath));
    }

    private function isIteratedPath(string $path): bool
    {
        $pathCheck = is_file($path)
            ? fn (string $file, string $path): string => $file === $path ? 'FOUND' : $path
            : fn (string $file, string $path): string => strpos($file, $path) === 0 ? 'FOUND' : $path;
        foreach (FixerSetup::config()->getFinder() as $file) {
            $path = $pathCheck($file->getPathname(), $path);
            if ($path === 'FOUND') { return true; }
        }
        return false;
    }

    private function packagePath(string $relativePath): string
    {
        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR));
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;
    }
}
