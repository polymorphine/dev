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
use Polymorphine\Dev\FixerFactory;


/** @group integrated */
class CompoundFixerTest extends TestCase
{
    private static Fixtures\FixerTestRunner $runner;

    public static function setUpBeforeClass(): void
    {
        $config = FixerFactory::createFor(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cs-fixer.php.dist');
        self::$runner = Fixtures\FixerTestRunner::withConfig($config);
    }

    /** @dataProvider fileList */
    public function test_FixedFiles_MatchExpectations(string $fileExpected, string $fileGiven)
    {
        self::assertSame(file_get_contents($fileExpected), self::$runner->fix(file_get_contents($fileGiven)));
    }

    public static function fileList(): iterable
    {
        $files = [];
        foreach (array_diff(scandir(__DIR__ . '/Fixtures/code-samples/Fixer'), ['..', '.']) as $file) {
            [$type, $index] = explode('-', $file, 2) + [false, false];
            $id = ($type === 'expected') ? 0 : 1;
            isset($files[$index]) or $files[$index] = [];
            $files[$index][$id] = __DIR__ . '/Fixtures/code-samples/Fixer/' . $file;
        }

        return $files;
    }
}
