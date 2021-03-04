<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tools;

use PHP_CodeSniffer\Files;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions;

require_once dirname(dirname(__DIR__)) . '/vendor/squizlabs/php_codesniffer/autoload.php';
if (!defined('PHP_CODESNIFFER_CBF')) {
    define('PHP_CODESNIFFER_CBF', false);
}


final class SnifferTokens
{
    use ArrayDump;

    /**
     * @param string|null $configFile
     *
     * @throws Exceptions\DeepExitException
     *
     * @return Runner
     */
    public static function runner(string $configFile = null): Runner
    {
        $configFile = $configFile ?: dirname(dirname(__DIR__)) . '/phpcs.xml.dist';
        $runner     = new Runner();
        $runner->config = new Config(['-q', '--standard=' . $configFile]);
        $runner->init();

        return $runner;
    }

    /**
     * @param string      $sourceCode Php code
     * @param string|null $dumpFile
     */
    public static function dumpSourceCode(string $sourceCode, ?string $dumpFile = null): void
    {
        $sourceFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        file_put_contents($sourceFile, $sourceCode);

        self::dumpSourceFile($sourceFile, $dumpFile);
        unlink($sourceFile);
    }

    /**
     * @param string      $sourceFile File with php code
     * @param string|null $dumpFile
     *
     * @throws Exceptions\DeepExitException
     */
    public static function dumpSourceFile(string $sourceFile, string $dumpFile = null): void
    {
        $runner = self::runner();
        $runner->ruleset->populateTokenListeners();

        $testFile = new Files\LocalFile($sourceFile, $runner->ruleset, $runner->config);
        $testFile->process();

        self::dump($testFile, $dumpFile);
    }

    /**
     * @param Files\File  $tokens     Processed php code file
     * @param string|null $tokensFile
     */
    public static function dump(Files\File $tokens, ?string $tokensFile = null): void
    {
        $tokens = $tokens->getTokens();
        foreach ($tokens as $id => &$token) {
            $token = ['idx' => $id] + $token;
        }

        self::json($tokens, $tokensFile ?: dirname(dirname(__DIR__)) . '/temp/tokens-dump.json');
    }
}
