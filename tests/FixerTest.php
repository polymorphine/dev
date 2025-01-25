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
use Polymorphine\Dev\Tests\Fixtures\FixerTestRunner;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use SplFileInfo;


abstract class FixerTest extends TestCase
{
    public function test_Properties()
    {
        $fixer = $this->fixer();
        $this->assertFalse($fixer->isRisky());
        $this->assertTrue($fixer->supports(new SplFileInfo(__FILE__)));
        $this->assertInstanceOf(FixerDefinitionInterface::class, $fixer->getDefinition());

        $properties = $this->properties();
        $this->assertSame($properties['name'], $fixer->getName());
        $this->assertSame($properties['priority'], $fixer->getPriority());
    }

    public function assertFixed(string $code, string $expected): void
    {
        $this->assertSame($expected, $this->fixerTestRunner()->fix($code));
    }

    public function assertUnchanged(string $code): void
    {
        $this->assertFixed($code, $code);
    }

    abstract protected function fixer(): FixerInterface;

    abstract protected function properties(): array;

    private function fixerTestRunner(): FixerTestRunner
    {
        return new FixerTestRunner([$this->fixer()]);
    }
}
