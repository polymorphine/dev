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
use Polymorphine\CodeStandards\Sniffs\CustomPrototypeSniff;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Config;

require_once dirname(dirname(__DIR__)) . '/vendor/squizlabs/php_codesniffer/autoload.php';


class CustomPrototypeSniffTest extends TestCase
{
    public function testInvalidClassNameGivesWarning()
    {
        $sniffer = new Runner();
        $sniffer->config = new Config(['-q']);
        $sniffer->init();

        $sniffer->ruleset->sniffs = [
            CustomPrototypeSniff::class => CustomPrototypeSniff::class
        ];
        $sniffer->ruleset->populateTokenListeners();

        $testFile = new LocalFile(dirname(__DIR__) . '/Files/Sniffs/InvalidClassName.php', $sniffer->ruleset, $sniffer->config);
        $testFile->process();

        $this->assertEquals([3], array_keys($testFile->getWarnings()));
    }
}
