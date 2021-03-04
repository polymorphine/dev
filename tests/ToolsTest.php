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
    public function testDumpSourceCodeSnifferTokens()
    {
        $testFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        $code     = '<?php declare(strict_types=1);';
        Tools\SnifferTokens::dumpSourceCode($code, $testFile);
        $this->assertTrue(file_exists($testFile));
        unlink($testFile);
    }

    public function testDumpSourceFileFixerTokens()
    {
        $testSourceFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        $testDumpFile   = tempnam(sys_get_temp_dir(), 'tmp_') . '.json';

        $code = '<?php declare(strict_types=1);';
        file_put_contents($testSourceFile, $code);

        Tools\FixerTokens::dumpSourceFile($testSourceFile, $testDumpFile);
        $this->assertTrue(file_exists($testDumpFile));
        unlink($testSourceFile);
        unlink($testDumpFile);
    }
}
