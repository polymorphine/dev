<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Fixtures\Tools;

use PHP_CodeSniffer\Files;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions;
use PHP_CodeSniffer\Tokenizers\PHP;
use PHP_CodeSniffer\Util\Tokens;

require_once dirname(__DIR__, 3) . '/vendor/squizlabs/php_codesniffer/autoload.php';
defined('PHP_CODESNIFFER_CBF') or define('PHP_CODESNIFFER_CBF', false);
defined('PHP_CODESNIFFER_VERBOSITY') or define('PHP_CODESNIFFER_VERBOSITY', 0);


final class SnifferTokens
{
    use ArrayDump;

    /**
     * @param null|string $configFile
     *
     * @throws Exceptions\DeepExitException
     *
     * @return Runner
     */
    public static function runner(?string $configFile = null): Runner
    {
        $configFile = $configFile ?: dirname(__DIR__) . '/tests.phpcs.xml';
        $runner     = new Runner();
        $runner->config = new Config(['-q', '--standard=' . $configFile]);
        $runner->init();

        return $runner;
    }

    /**
     * @param string $fileContents
     * @param bool   $simplified   If true extended token data will be added
     *
     * @throws Exceptions\TokenizerException
     *
     * @return array<int, array<string, mixed>> Tokens
     */
    public static function fromCode(string $fileContents, bool $simplified = true): array
    {
        if ($simplified) {
            if (!class_exists(Tokens::class)) { return []; }
            $tokenizer = new PHP($fileContents, null);
            return $tokenizer->getTokens();
        }

        $sourceFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        file_put_contents($sourceFile, $fileContents);
        $tokens = self::tokenizedFile($sourceFile)->getTokens();
        unlink($sourceFile);
        return $tokens;
    }

    /**
     * @param string $sourceFile
     *
     * @throws Exceptions\DeepExitException
     *
     * @return Files\File
     */
    public static function tokenizedFile(string $sourceFile): Files\File
    {
        $runner = self::runner();
        $runner->ruleset->populateTokenListeners();

        $testFile = new Files\LocalFile($sourceFile, $runner->ruleset, $runner->config);
        $testFile->process();

        return $testFile;
    }

    /**
     * @param string      $sourceCode Php code
     * @param null|string $dumpFile
     */
    public static function dumpSourceCode(string $sourceCode, ?string $dumpFile = null): void
    {
        self::dump(self::fromCode($sourceCode, false), $dumpFile);
    }

    /**
     * @param string      $sourceFile File with php code
     * @param null|string $dumpFile
     *
     * @throws Exceptions\DeepExitException
     */
    public static function dumpSourceFile(string $sourceFile, ?string $dumpFile = null): void
    {
        self::dump(self::tokenizedFile($sourceFile)->getTokens(), $dumpFile);
    }

    /**
     * @param array<int, array<string, mixed>> $tokens     Processed php code file
     * @param null|string                      $tokensFile
     */
    public static function dump(array $tokens, ?string $tokensFile = null): void
    {
        foreach ($tokens as $id => &$token) {
            $token = ['idx' => $id] + $token;
        }
        self::json($tokens, $tokensFile);
    }
}
