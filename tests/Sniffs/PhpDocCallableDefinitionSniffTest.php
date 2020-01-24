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
use Polymorphine\CodeStandards\Sniffs\PhpDocCallableDefinitionSniff;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Config;

require_once dirname(dirname(__DIR__)) . '/vendor/squizlabs/php_codesniffer/autoload.php';


class PhpDocCallableDefinitionSniffTest extends TestCase
{
    public function testCallableParamDocWithoutDefinitionGivesWarning()
    {
        $sniffer = new Runner();
        $sniffer->config = new Config(['-q']);
        $sniffer->init();

        $sniffer->ruleset->sniffs = [
            PhpDocCallableDefinitionSniff::class => PhpDocCallableDefinitionSniff::class
        ];
        $sniffer->ruleset->populateTokenListeners();

        $fileName = dirname(__DIR__) . '/Files/Sniffs/PhpDocCallableDefinitions.php';
        $testFile = new LocalFile($fileName, $sniffer->ruleset, $sniffer->config);
        $testFile->process();

        $this->assertEquals([20, 28], array_keys($testFile->getWarnings()));
    }
}
