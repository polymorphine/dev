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

use PHP_CodeSniffer\Files\LocalFile;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;
use Polymorphine\Dev\Tools;


class ToolsTest extends TestCase
{
    public function test_DumpSourceCodeSnifferTokens()
    {
        $directory = sys_get_temp_dir();
        $codeFile  = tempnam($directory, 'tmp_0') . '.php';
        $dumpFileA = tempnam($directory, 'tmp_1') . '.json';
        $dumpFileB = tempnam($directory, 'tmp_2') . '.json';
        $code      = '<?php declare(strict_types=1);';
        file_put_contents($codeFile, $code);
        Tools\SnifferTokens::dumpSourceCode($code, $dumpFileA);
        Tools\SnifferTokens::dumpSourceFile($codeFile, $dumpFileB);
        $this->assertSame(file_get_contents($dumpFileA), file_get_contents($dumpFileB));
        unlink($codeFile);
        unlink($dumpFileA);
        unlink($dumpFileB);
    }

    public function test_TokenizedCodeSnifferFile()
    {
        $this->assertInstanceOf(LocalFile::class, Tools\SnifferTokens::tokenizedFile(__FILE__));
    }

    public function test_DumpSourceCodeFixerTokens()
    {
        $directory = sys_get_temp_dir();
        $codeFile  = tempnam($directory, 'tmp_0') . '.php';
        $dumpFileA = tempnam($directory, 'tmp_1') . '.json';
        $dumpFileB = tempnam($directory, 'tmp_2') . '.json';
        $code      = '<?php declare(strict_types=1);';
        file_put_contents($codeFile, $code);
        Tools\FixerTokens::dumpSourceCode($code, $dumpFileA);
        Tools\FixerTokens::dumpSourceFile($codeFile, $dumpFileB);
        $this->assertSame(file_get_contents($dumpFileA), file_get_contents($dumpFileB));
        unlink($codeFile);
        unlink($dumpFileA);
        unlink($dumpFileB);
    }

    public function test_TokenizedCodeFixerFile()
    {
        $this->assertInstanceOf(Tokens::class, Tools\FixerTokens::tokenizedFile(__FILE__));
    }

    /** @dataProvider phpDocLines */
    public function test_TypesFromPhpDocLine_AreReduced(string $line, string $reduced, string $simplified, string $var)
    {
        $line = new Tools\PhpDocTypeLine($line);
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
