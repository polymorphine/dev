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
use Polymorphine\Dev\Sniffer\Sniffs\PhpDoc\ValidTagsSniff;


class ValidTagsSniffTest extends SnifferTest
{
    public function test_MinorPhpDocTagMismatches_ReportWarnings()
    {
        $warnings = [15 => 'Sniffer.PhpDoc.ValidTags.ArgumentOrder'];
        $this->assertWarningLines($warnings, 'PhpDocTagValidation.php');
    }

    public function test_InvalidPhpDoc_ReportErrors()
    {
        $errors = [
            26 => 'Sniffer.PhpDoc.ValidTags.Invalid',
            21 => 'Sniffer.PhpDoc.ValidTags.Required',
            33 => 'Sniffer.PhpDoc.ValidTags.Unexpected',
            40 => 'Sniffer.PhpDoc.ValidTags.Required'
        ];
        $this->assertErrorLines($errors, 'PhpDocTagValidation.php');
    }

    protected function sniffClass(): string
    {
        return ValidTagsSniff::class;
    }
}
