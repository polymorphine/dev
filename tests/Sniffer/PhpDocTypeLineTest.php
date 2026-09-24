<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Sniffer;

use PHPUnit\Framework\TestCase;
use Polymorphine\Dev\Sniffer\PhpDocTypeLine;


class PhpDocTypeLineTest extends TestCase
{
    /** @dataProvider phpDocLines */
    public function test_TypesFromPhpDocLine_AreReduced(string $line, string $reduced, string $simplified, string $var)
    {
        $line = new PhpDocTypeLine($line);
        $this->assertSame($reduced, $line->reducedType());
        $this->assertSame($simplified, $line->simplifiedType());
        $this->assertSame($var, $line->variableName());
    }

    public static function phpDocLines(): array
    {
        return [
            ['callable(): Test $variable Short explanation', 'T', 'callable', '$variable'],
            ['Foo|array<not, reduceable>> $foo commented type', 'T>', 'Foo|array>', '$foo'],
            ['Some\\Type<array<int>>', 'T', 'Some\\Type', ''],
            ['null|non-empty-list<mixed>', 'T', '?array', ''],
            ['int<0, 100> $percent', 'T', 'int', '$percent'],
            ['some-esoteric-type-Closure(Foo): void', 'T', 'Closure', ''],
            ['class-string $className FQN', 'T', 'string', '$className'],
            ['null|array<int, array<int, callable(array{foo: null|int, bar: \\Bar\\Baz\\F99}, int): ' .
             'array<int>>> $type: Overkill', 'T', '?array', '$type:']
        ];
    }
}
