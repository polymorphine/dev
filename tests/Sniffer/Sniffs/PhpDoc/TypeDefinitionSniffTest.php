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
use Polymorphine\Dev\Sniffer\Sniffs\PhpDoc\TypeDefinitionSniff;


class TypeDefinitionSniffTest extends SnifferTest
{
    public function test_CallableParamDoc_WithoutDefinition_GivesWarning()
    {
        $warnings = array_fill_keys(range(10, 28), 'Sniffer.PhpDoc.TypeDefinition.FoundCallback');
        $this->assertWarningLines($warnings, 'PhpDocCallableDefinitions.php');
    }

    public function test_ArrayParamDoc_WithoutDefinition_GivesWarning()
    {
        $warnings = array_fill_keys(array_merge(range(13, 21), [28]), 'Sniffer.PhpDoc.TypeDefinition.FoundArray');
        $this->assertWarningLines($warnings, 'PhpDocArrayDefinitions.php');
    }

    public function test_MalformedTypeParamDoc_GivesWarning()
    {
        $warnings = array_fill_keys(range(10, 17), 'Sniffer.PhpDoc.TypeDefinition.FoundMalformed');
        $this->assertWarningLines($warnings, 'PhpDocMalformedDefinitions.php');
    }

    protected function sniffClass(): string
    {
        return TypeDefinitionSniff::class;
    }
}
