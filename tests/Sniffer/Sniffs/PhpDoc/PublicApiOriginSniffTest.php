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
use Polymorphine\Dev\Sniffer\Sniffs\PhpDoc\PublicApiOriginSniff;


class PublicApiOriginSniffTest extends SnifferTest
{
    /** @dataProvider classFileWarnings */
    public function test_Interface_Warnings(array $warningLines, string $filename)
    {
        $this->assertWarningLines($warningLines, $filename);
    }

    public static function classFileWarnings(): iterable
    {
        return [
            'interface' => [[12], 'PhpDocInterface.php'],
            'class'     => [[14], 'PhpDocClass.php'],
            'parent'    => [[8], 'PhpDocParent.php'],
            'invalid'   => [[8], 'PhpDocInvalidClass.php']
        ];
    }

    protected function sniffClass(): string
    {
        return PublicApiOriginSniff::class;
    }
}
