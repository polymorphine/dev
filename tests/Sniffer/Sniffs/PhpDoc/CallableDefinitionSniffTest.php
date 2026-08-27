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
use Polymorphine\Dev\Sniffer\Sniffs\PhpDoc\CallableDefinitionSniff;


class CallableDefinitionSniffTest extends SnifferTest
{
    public function test_CallableParamDoc_WithoutDefinition_GivesWarning()
    {
        $this->assertWarningLines(range(10, 28), 'PhpDocCallableDefinitions.php');
    }

    protected function sniffClass(): string
    {
        return CallableDefinitionSniff::class;
    }
}
