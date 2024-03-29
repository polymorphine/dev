<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Sniffer\Sniffs\PhpDoc;

use Polymorphine\Dev\Tests\SnifferTest;
use Polymorphine\Dev\Sniffer\Sniffs\PhpDoc\RequiredForPublicApiSniff;


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

    public function classFileWarnings(): array
    {
        return [
            'interface' => ['./tests/Fixtures/code-samples/Sniffs/PhpDocRequiredForInterfaceApi.php', [12]],
            'class'     => ['./tests/Fixtures/code-samples/Sniffs/PhpDocRequiredForClassApi.php', [14]],
            'parent'    => ['./tests/Fixtures/code-samples/Sniffs/PhpDocRequiredForParentApi.php', [8]],
            'invalid'   => ['./tests/Fixtures/code-samples/Sniffs/PhpDocRequiredForInvalidClass.php', [8]]
        ];
    }

    protected function sniffer(): string
    {
        return RequiredForPublicApiSniff::class;
    }
}
