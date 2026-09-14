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
    /** @dataProvider APIDocWarnings */
    public function test_MissingPhpDocForOriginalApi_ReportsWarning(array $warningLines, string $filename)
    {
        $warnings = array_fill_keys($warningLines, 'Sniffer.PhpDoc.ValidTags.MissingAPI');
        $this->assertWarningLines($warnings, $filename);
    }

    /** @dataProvider APIDocErrors */
    public function test_MissingRequiredTypePhpDocForOriginalApi_ReportsError(array $warningLines, string $filename)
    {
        $warnings = array_fill_keys($warningLines, 'Sniffer.PhpDoc.ValidTags.RequiredAPI');
        $this->assertErrorLines($warnings, $filename);
    }

    public function test_TagOrderMismatch_ReportsWarning()
    {
        $warnings = [15 => 'Sniffer.PhpDoc.ValidTags.Order'];
        $this->assertWarningLines($warnings, 'PhpDocTagValidation.php');
    }

    public function test_InvalidPhpDoc_ReportsError()
    {
        $errors = [
            15 => 'Sniffer.PhpDoc.ValidTags.Invalid',
            26 => 'Sniffer.PhpDoc.ValidTags.Invalid',
            21 => 'Sniffer.PhpDoc.ValidTags.TypeRequired',
            33 => 'Sniffer.PhpDoc.ValidTags.Unexpected',
            40 => 'Sniffer.PhpDoc.ValidTags.TypeRequired'
        ];
        $this->assertErrorLines($errors, 'PhpDocTagValidation.php');
    }

    public static function APIDocWarnings(): iterable
    {
        return [
            'interface' => [[12], 'PhpDocInterface.php'],
            'class'     => [[21], 'PhpDocClass.php'],
            'parent'    => [[8, 10], 'PhpDocParent.php'],
            'invalid'   => [[8], 'PhpDocInvalidClass.php']
        ];
    }

    public static function APIDocErrors(): iterable
    {
        return [
            'interface' => [[13, 14], 'PhpDocInterface.php'],
            'class'     => [[13, 22, 29], 'PhpDocClass.php'],
            'parent'    => [[], 'PhpDocParent.php'],
            'invalid'   => [[], 'PhpDocInvalidClass.php']
        ];
    }

    protected function sniffClass(): string
    {
        return ValidTagsSniff::class;
    }
}
