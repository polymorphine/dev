<?php

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Fixtures;

use Polymorphine\Dev\Tools\SnifferTokens;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files;
use PHP_CodeSniffer\Util;


class SnifferTestRunner
{
    private Ruleset $ruleset;
    private Config  $config;
    private array   $properties;

    public function __construct(string $sniffClass)
    {
        $runner = SnifferTokens::runner(__DIR__ . '/tests.phpcs.xml');

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
