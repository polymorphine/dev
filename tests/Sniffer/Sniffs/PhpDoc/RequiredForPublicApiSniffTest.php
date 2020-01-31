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
    /**
     * @dataProvider classFileWarnings
     *
     * @param string $filename
     * @param array  $warningLines
     */
    public function testInterfaceWarnings(string $filename, array $warningLines)
    {
        $this->assertWarningLines($filename, $warningLines);
    }

    public function classFileWarnings()
    {
        return [
            'interface' => ['./tests/Files/Sniffs/PhpDocRequiredForInterfaceApi.php', [12]],
            'class'     => ['./tests/Files/Sniffs/PhpDocRequiredForClassApi.php', [14]],
            'parent'    => ['./tests/Files/Sniffs/PhpDocRequiredForParentApi.php', [8]],
            'invalid'   => ['./tests/Files/Sniffs/PhpDocRequiredForInvalidClass.php', [8]]
        ];
    }

    protected function sniffer(): string
    {
        return RequiredForPublicApiSniff::class;
    }
}
