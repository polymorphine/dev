<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Sniffer\Sniffs\NamingConventions;

use Polymorphine\Dev\Tests\SnifferTest;
use Polymorphine\Dev\Sniffer\Sniffs\NamingConventions\ValidVariableNameSniff;


class ValidVariableNameSniffTest extends SnifferTest
{
    public function test_LocalVariableName_WithNumber_GivesWarning()
    {
        $warningLines = [16, 17, 34, 36, 56, 68];
        $this->assertWarningLines($warningLines, 'InvalidVariableNames.php');
    }

    public function test_ClassVariableName_WithNoCamelCaseOrNumber_GivesError()
    {
        $errorLines = [13, 14, 15, 24, 25, 26, 27, 30, 31, 32, 34, 36, 43, 44, 53, 59, 60, 61, 69, 85];
        $this->assertErrorLines($errorLines, 'InvalidVariableNames.php');
    }

    protected function sniffClass(): string
    {
        return ValidVariableNameSniff::class;
    }
}
