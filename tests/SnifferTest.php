<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests;

use PHPUnit\Framework\TestCase;
use PHP_CodeSniffer\Files\File;
use Polymorphine\Dev\Tests\Fixtures\SnifferTestRunner;


abstract class SnifferTest extends TestCase
{
    public function assertWarningLines(array $expectedWarningLines, string $filename, array $options = []): void
    {
        $file = $this->sniffedFile($filename, $options);
        if (!isset($expectedWarningLines[0])) {
            $this->assertReportCodes($expectedWarningLines, $file->getWarnings());
            $expectedWarningLines = array_keys($expectedWarningLines);
        }
        $this->assertEquals($expectedWarningLines, array_keys($file->getWarnings()));
    }

    public function assertErrorLines(array $expectedErrorLines, string $filename, array $options = []): void
    {
        $file = $this->sniffedFile($filename, $options);
        if (!isset($expectedErrorLines[0])) {
            $this->assertReportCodes($expectedErrorLines, $file->getErrors());
            $expectedErrorLines = array_keys($expectedErrorLines);
        }
        $this->assertEquals($expectedErrorLines, array_keys($file->getErrors()));
    }

    abstract protected function sniffClass(): string;

    private function sniffedFile(string $filename, array $options = []): File
    {
        $runner = new SnifferTestRunner($this->sniffClass(), $options);
        return $runner->sniff('./tests/Fixtures/code-samples/Sniffs/' . $filename);
    }

    private function assertReportCodes(array $errorCodes, array $reportList): void
    {
        foreach ($reportList as $line => $column) {
            $codes = [];
            foreach ($column as $warnings) {
                foreach ($warnings as $warning) {
                    $codes[] = $warning['source'];
                }
            }
            $this->assertContains($errorCodes[$line] ?? [], $codes, 'Line: ' . $line);
        }
    }
}
