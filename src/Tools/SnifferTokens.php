<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tools;

use PHP_CodeSniffer\Files;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Config;

require_once dirname(dirname(__DIR__)) . '/vendor/squizlabs/php_codesniffer/autoload.php';


final class SnifferTokens
{
    use ArrayDump;

    public static function dumpSourceCode(string $sourceCode, ?string $dumpFile = null): void
    {
        $sourceFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        file_put_contents($sourceFile, $sourceCode);

        self::dumpSourceFile($sourceFile, $dumpFile);
        unlink($sourceFile);
    }

    public static function dumpSourceFile(string $sourceFile, string $dumpFile = null): void
    {
        $runner = new Runner();
        $runner->config = new Config(['-q']);
        $runner->init();

        $runner->ruleset->populateTokenListeners();

        $testFile = new Files\LocalFile($sourceFile, $runner->ruleset, $runner->config);
        $testFile->process();

        self::dump($testFile, $dumpFile);
    }

    public static function dump(Files\File $tokens, ?string $tokensFile = null): void
    {
        $tokens = $tokens->getTokens();
        foreach ($tokens as $id => &$token) {
            $token = ['idx' => $id] + $token;
        }

        self::json($tokens, $tokensFile ?: dirname(dirname(__DIR__)) . '/temp/tokens-dump.json');
    }
}
