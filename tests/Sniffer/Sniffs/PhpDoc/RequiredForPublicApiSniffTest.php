<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests\Sniffer\Sniffs\PhpDoc;

use Polymorphine\CodeStandards\Tests\SnifferTest;
use Polymorphine\CodeStandards\Sniffer\Sniffs\PhpDoc\RequiredForPublicApiSniff;


class RequiredForPublicApiSniffTest extends SnifferTest
{
    public function testInterfaceWarnings()
    {
        $filename = './tests/Files/Sniffs/PhpDocRequiredForInterfaceApi.php';
        $this->assertWarningLines($filename, [13]);
    }

    public function testClassWarnings()
    {
        $filename = './tests/Files/Sniffs/PhpDocRequiredForClassApi.php';
        $this->assertWarningLines($filename, [11]);
    }

    protected function sniffer(): string
    {
        return RequiredForPublicApiSniff::class;
    }
}
