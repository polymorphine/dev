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
use Polymorphine\Dev\Tests\Fixtures\SnifferTestRunner;


abstract class SnifferTest extends TestCase
{
    private SnifferTestRunner $runner;

    public function setUp(): void
    {
        $this->runner = new SnifferTestRunner($this->sniffer());
    }

    public function setProperties(array $properties): void
    {
        $this->runner->setProperties($properties);
    }

    public function assertWarningLines(string $filename, array $expectedWarningLines): void
    {
        $fileWarnings = $this->runner->sniff($filename)->getWarnings();
        $this->assertEquals($expectedWarningLines, array_keys($fileWarnings));
    }

    public function assertErrorLines(string $filename, array $expectedErrorLines): void
    {
        $fileErrors = $this->runner->sniff($filename)->getErrors();
        $this->assertEquals($expectedErrorLines, array_keys($fileErrors));
    }

    abstract protected function sniffer(): string;
}
