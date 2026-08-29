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
use Polymorphine\Dev\Sniffer\Sniffs\PhpDoc\ArrayDefinitionSniff;


class ArrayDefinitionSniffTest extends SnifferTest
{
    public function test_ArrayParamDoc_WithoutDefinition_GivesWarning()
    {
        $this->assertWarningLines(range(10, 18), 'PhpDocArrayDefinitions.php');
    }

    protected function sniffClass(): string
    {
        return ArrayDefinitionSniff::class;
    }
}
