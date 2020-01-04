<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests;

use PHPUnit\Framework\TestCase;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use SplFileInfo;


abstract class FixerTest extends TestCase
{
    protected TestRunner $runner;

    protected function setUp(): void
    {
        $fixer = $this->fixer();
        $this->runner = new TestRunner([$fixer]);
    }

    public function testProperties()
    {
        $fixer = $this->fixer();
        $this->assertFalse($fixer->isRisky());
        $this->assertTrue($fixer->supports(new SplFileInfo(__FILE__)));
        $this->assertInstanceOf(FixerDefinition::class, $fixer->getDefinition());

        $properties = $this->properties();
        $this->assertSame($properties['name'], $fixer->getName());
        $this->assertSame($properties['priority'], $fixer->getPriority());
    }

    abstract protected function fixer(): FixerInterface;

    abstract protected function properties(): array;
}
