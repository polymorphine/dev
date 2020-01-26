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

use PHP_CodeSniffer\Files\File;
use PHPUnit\Framework\TestCase;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Util\Common as Util;
use PHP_CodeSniffer\Files\LocalFile;

require_once dirname(__DIR__) . '/vendor/squizlabs/php_codesniffer/autoload.php';


abstract class SnifferTest extends TestCase
{
    private Ruleset $ruleset;
    private Config  $config;

    public function setUp(): void
    {
        $runner = new Runner();
        $runner->config = new Config(['-q']);
        $runner->init();

        $this->ruleset = $runner->ruleset;
        $this->config  = $runner->config;

        $this->ruleset->sniffs[$this->sniffer()] = true;
    }

    public function setProperties(array $properties)
    {
        $code = Util::getSniffCode($this->sniffer());
        $this->ruleset->ruleset[$code]['properties'] = $properties;
    }

    public function assertWarningLines(string $filename, array $expectedWarningLines)
    {
        $actualWarningLines = array_keys($this->processedFile($filename)->getWarnings());
        $this->assertEquals($expectedWarningLines, $actualWarningLines);
    }

    abstract protected function sniffer(): string;

    protected function dumpTokens(string $filename, string $tokensFile = null): void
    {
        $tokens = $this->processedFile($filename)->getTokens();
        foreach ($tokens as $id => &$token) {
            $token = ['idx' => $id] + $token;
        }

        $tokensFile = $tokensFile ?: dirname(__DIR__) . '/temp/tokens-dump.json';
        file_put_contents($tokensFile, json_encode($tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function processedFile(string $filename): File
    {
        $this->ruleset->populateTokenListeners();

        $testFile = new LocalFile($filename, $this->ruleset, $this->config);
        $testFile->process();

        return $testFile;
    }
}
