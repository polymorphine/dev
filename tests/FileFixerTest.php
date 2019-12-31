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
use Polymorphine\CodeStandards\FixerFactory;


/**
 * @group integrated
 */
class FileFixerTest extends TestCase
{
    private $runner;

    protected function setUp(): void
    {
        $config = FixerFactory::createFor('Polymorphine/CodeStandards', __DIR__);
        $this->runner = Fixtures\TestRunner::withConfig($config);
    }

    /**
     * @dataProvider fileList
     *
     * @param string $fileExpected
     * @param string $fileGiven
     */
    public function testFixedFiles_MatchExpectations($fileExpected, $fileGiven)
    {
        $sourceCode = file_get_contents($fileGiven);
        $this->assertSame(file_get_contents($fileExpected), $this->runner->fix($sourceCode));
    }

    public function fileList()
    {
        $files = [];
        foreach (array_diff(scandir(__DIR__ . '/Files'), ['..', '.']) as $file) {
            [$type, $index] = explode('-', $file, 2) + [false, false];
            $id = ($type === 'expected') ? 0 : 1;
            isset($files[$index]) or $files[$index] = [];
            $files[$index][$id] = __DIR__ . '/Files/' . $file;
        }

        return $files;
    }
}
