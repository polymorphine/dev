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
    public function testNumberInLocalVariableName_GivesWarning()
    {
        $lines = [16, 17, 34, 36, 56, 68];
        $this->assertWarningLines('./tests/Fixtures/code-samples/Sniffs/InvalidVariableNames.php', $lines);
    }

    public function testInvalidCamelCaseOrNumberInClassVariable_GivesError(): void
    {
        $lines = [13, 14, 15, 24, 25, 26, 27, 30, 31, 32, 34, 36, 43, 44, 53, 59, 60, 61, 69, 85];
        $this->assertErrorLines('./tests/Fixtures/code-samples/Sniffs/InvalidVariableNames.php', $lines);
    }

    protected function sniffer(): string
    {
        return ValidVariableNameSniff::class;
    }
}
