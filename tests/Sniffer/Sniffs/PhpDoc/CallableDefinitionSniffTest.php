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

use Polymorphine\CodeStandards\Tests\SnifferTest;
use Polymorphine\CodeStandards\Sniffer\Sniffs\PhpDoc\CallableDefinitionSniff;


class CallableDefinitionSniffTest extends SnifferTest
{
    /**
     * @dataProvider properties
     *
     * @param array $properties
     * @param int[] $expectedWarningLines
     */
    public function testCallableParamDocWithoutDefinitionGivesWarning(array $properties, array $expectedWarningLines)
    {
        $this->setProperties($properties);
        $this->assertWarningLines('./tests/Files/Sniffs/PhpDocCallableDefinitions.php', $expectedWarningLines);
    }

    public function properties()
    {
        return [
            [['syntax' => 'both', 'includeClosure' => true], [20, 28]],
            [['syntax' => 'both', 'includeClosure' => false], [20]],
            [['syntax' => 'short', 'includeClosure' => false], [20, 42]],
            [['syntax' => 'long', 'includeClosure' => true], [11, 20, 21, 28, 35]]
        ];
    }

    protected function sniffer(): string
    {
        return CallableDefinitionSniff::class;
    }
}
