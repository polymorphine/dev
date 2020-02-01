<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests\Fixtures;

use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files;
use PHP_CodeSniffer\Util;

require_once dirname(dirname(__DIR__)) . '/vendor/squizlabs/php_codesniffer/autoload.php';
if (!defined('PHP_CODESNIFFER_CBF')) {
    define('PHP_CODESNIFFER_CBF', false);
}


class SnifferTestRunner
{
    private Ruleset $ruleset;
    private Config  $config;
    private array   $properties;

    public function __construct(string $sniffClass)
    {
        $runner = new Runner();
        $runner->config = new Config(['-q', '--standard=' . __DIR__ . '/tests.phpcs.xml']);
        $runner->init();

        $this->ruleset = $runner->ruleset;
        $this->config  = $runner->config;

        $this->ruleset->sniffs[$sniffClass] = true;

        $code = Util\Common::getSniffCode($sniffClass);
        $this->ruleset->ruleset[$code]['properties'] = [];
        $this->properties = &$this->ruleset->ruleset[$code]['properties'];
    }

    public function sniff(string $filename): Files\File
    {
        $this->ruleset->populateTokenListeners();

        $testFile = new Files\LocalFile($filename, $this->ruleset, $this->config);
        $testFile->process();

        return $testFile;
    }

    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }
}
