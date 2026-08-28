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
use Polymorphine\Dev\Tools;


class ToolsTest extends TestCase
{
    public function test_DumpSourceCodeSnifferTokens()
    {
        $testFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        $code     = '<?php declare(strict_types=1);';
        Tools\SnifferTokens::dumpSourceCode($code, $testFile);
        $this->assertTrue(file_exists($testFile));
        unlink($testFile);
    }

    public function test_DumpSourceFileFixerTokens()
    {
        $directory      = sys_get_temp_dir();
        $testSourceFile = tempnam($directory, 'tmp_') . '.php';
        $testDumpFileA  = tempnam($directory, 'tmp_1') . '.json';
        $testDumpFileB  = tempnam($directory, 'tmp_2') . '.json';

        $code = '<?php declare(strict_types=1);';
        file_put_contents($testSourceFile, $code);

        Tools\FixerTokens::dumpSourceFile($testSourceFile, $testDumpFileA);
        Tools\FixerTokens::dumpSourceCode($code, $testDumpFileB);
        $this->assertTrue(file_exists($testDumpFileA));
        $this->assertSame(file_get_contents($testDumpFileA), file_get_contents($testDumpFileB));
        unlink($testSourceFile);
        unlink($testDumpFileA);
        unlink($testDumpFileB);
    }
}
