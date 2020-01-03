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

use PHPUnit\Framework\TestCase;
use Polymorphine\CodeStandards\Fixer\AlignedAssignmentsFixer;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


class AlignedAssignmentsFixerTest extends TestCase
{
    private TestRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new TestRunner([new AlignedAssignmentsFixer()]);
    }

    public function testVariableAssignmentsAreAligned()
    {
        $code = <<<'CODE'
            <?php

            $x = 10;
            $bool = true;
            $another = 'string';

            CODE;

        $expected = <<<'CODE'
            <?php

            $x       = 10;
            $bool    = true;
            $another = 'string';

            CODE;
        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMixedKindVariableAssignmentsAreAlignedSeparately()
    {
        $code = <<<'CODE'
            <?php

            $x = 10;
            $bool = true;
            $array['key_assoc'] = true;
            $this->thing = 'string';
            $this->foo = 'bar';
            SomeClass::$var = true;
            Another::$foo = 'bar';
            $array['key_assoc'] = true;
            $arrayWithKey['another'] = 'aligned';

            CODE;

        $expected = <<<'CODE'
            <?php

            $x    = 10;
            $bool = true;
            $array['key_assoc'] = true;
            $this->thing = 'string';
            $this->foo   = 'bar';
            SomeClass::$var = true;
            Another::$foo   = 'bar';
            $array['key_assoc']      = true;
            $arrayWithKey['another'] = 'aligned';

            CODE;
        $this->assertSame($expected, $this->runner->fix($code));
    }
}
