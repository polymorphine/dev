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
    /** @dataProvider classFileWarnings */
    public function test_Interface_Warnings(string $filename, array $warningLines)
    {
        $this->assertWarningLines('./tests/Fixtures/code-samples/Sniffs/' . $filename, $warningLines);
    }

    public static function classFileWarnings(): iterable
    {
        return [
            'interface' => ['PhpDocRequiredForInterfaceApi.php', [12]],
            'class'     => ['PhpDocRequiredForClassApi.php', [14]],
            'parent'    => ['PhpDocRequiredForParentApi.php', [8]],
            'invalid'   => ['PhpDocRequiredForInvalidClass.php', [8]]
        ];
    }

    protected function sniffer(): string
    {
        return RequiredForPublicApiSniff::class;
    }
}
