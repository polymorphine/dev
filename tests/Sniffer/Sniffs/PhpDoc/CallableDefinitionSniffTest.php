<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests\Sniffer\Sniffs\PhpDoc;

use PHPUnit\Framework\TestCase;
use Polymorphine\CodeStandards\Sniffer\Sniffs\PhpDoc\CallableDefinitionSniff;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Util\Common as Util;

require_once __DIR__ . '/../../../../vendor/squizlabs/php_codesniffer/autoload.php';


class CallableDefinitionSniffTest extends TestCase
{
    /**
     * @dataProvider properties
     *
     * @param array $properties
     * @param int[] $expectedWarningLines
     */
    public function testCallableParamDocWithoutDefinitionGivesWarning(array $properties, array $expectedWarningLines)
    {
        $runner = new Runner();
        $runner->config = new Config(['-q']);
        $runner->init();

        $class = CallableDefinitionSniff::class;
        $code  = Util::getSniffCode($class);

        $runner->ruleset->sniffs[$class] = true;
        $runner->ruleset->ruleset[$code]['properties'] = $properties;
        $runner->ruleset->populateTokenListeners();

        $fileName = './tests/Files/Sniffs/PhpDocCallableDefinitions.php';
        $testFile = new LocalFile($fileName, $runner->ruleset, $runner->config);
        $testFile->process();

        $this->assertEquals($expectedWarningLines, array_keys($testFile->getWarnings()));
    }

    public function properties()
    {
        return [
            [['shortSyntax' => true, 'longSyntax' => true, 'describeClosure' => true], [20, 28]],
            [['shortSyntax' => true, 'longSyntax' => true, 'describeClosure' => false], [20]],
            [['shortSyntax' => true, 'longSyntax' => false, 'describeClosure' => false], [20, 42]],
            [['shortSyntax' => false, 'longSyntax' => true, 'describeClosure' => true], [11, 20, 21, 28, 35]]
        ];
    }
}
