<?php

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
use Polymorphine\Dev\Tests\Fixtures\FixerTestRunner;
use PhpCsFixer\Fixer\FixerInterface;
use SplFileInfo;


abstract class FixerTest extends TestCase
{
    protected FixerTestRunner $runner;

    protected function setUp(): void
    {
        $fixer = $this->fixer();
        $this->runner = new FixerTestRunner([$fixer]);
    }

    public function testProperties()
    {
        $fixer = $this->fixer();
        $this->assertFalse($fixer->isRisky());
        $this->assertTrue($fixer->supports(new SplFileInfo(__FILE__)));

        $properties = $this->properties();
        $this->assertSame($properties['name'], $fixer->getName());
        $this->assertSame($properties['priority'], $fixer->getPriority());
    }

    abstract protected function fixer(): FixerInterface;

    abstract protected function properties(): array;
}
