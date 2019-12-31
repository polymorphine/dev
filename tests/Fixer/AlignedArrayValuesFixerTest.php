<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests\Fixer;

use PHPUnit\Framework\TestCase;
use Polymorphine\CodeStandards\Fixer\AlignedArrayValuesFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class AlignedArrayValuesFixerTest extends TestCase
{
    private $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new AlignedArrayValuesFixer()]);
    }

    public function testNonAssociativeArraysAreNotChanged()
    {
        $code = $this->code(<<<'PHP'
            $x = [
                'a', 'abc',
                'def'
            ];
            PHP);
        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testSingleLineArraysAreNotChanged()
    {
        $code = $this->code(<<<'PHP'
            $x = ['a' => 10, 'abc' => 20];

            PHP);
        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testMultilineArraysAreAligned()
    {
        $code = $this->code(<<<'PHP'
            $x = [
                'a' => 10,
                'abc' => ['foo' => $x, 'bar' => $y],
                'foo-bar' => 12,
                'baz' => 1
            ];
            PHP);

        $expected = $this->code(<<<'PHP'
            $x = [
                'a'       => 10,
                'abc'     => ['foo' => $x, 'bar' => $y],
                'foo-bar' => 12,
                'baz'     => 1
            ];
            PHP);
        $this->assertSame($expected, $this->runner->fix($code));
    }

    private function code(string $code): string
    {
        return '<?php' . PHP_EOL . $code;
    }
}
