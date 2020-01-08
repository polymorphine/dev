<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tests\Fixer;

use Polymorphine\CodeStandards\Tests\FixerTest;
use Polymorphine\CodeStandards\Fixer\ShortConditionsSingleLineFixer;


class ShortConditionsSingleLineFixerTest extends FixerTest
{
    public function testShortConditionsAreTurnedIntoSingleLine()
    {
        $code = <<<'CODE'
            <?php
            
            if ($variable !== CONSTANT_VALUE) {
                return;
            }
            
            CODE;

        $expected = <<<'CODE'
            <?php
            
            if ($variable !== CONSTANT_VALUE) { return; }
            
            CODE;

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testLongConditionsAreNotChanged()
    {
        $code = <<<'CODE'
            <?php
            
            if ($variable !== CONSTANT_VALUE) {
                return $expression + $secondExpression;
            }
            
            if ($true) {
                return true;
            } else {
                return false;
            }
            
            if ($veryLongVariableThatWillCauseLineLimitExceed) {
                return 'string with spaces';
            }
            
            if ($variable) {
                return 'String 19+ chars...';
            }
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    protected function fixer(): ShortConditionsSingleLineFixer
    {
        return new ShortConditionsSingleLineFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/short_conditions_single_line', 'priority' => -40];
    }
}
