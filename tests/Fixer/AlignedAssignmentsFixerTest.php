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


class AlignedAssignmentsFixerTest extends TestCase
{
    use FixerTestMethods;

    protected function setUp(): void
    {
        $this->setRunner(new AlignedAssignmentsFixer());
    }

    public function testVariableAssignmentsAreAligned()
    {
        $code = $this->code(<<<'PHP'

            $x = 10;
            $bool = true;
            $another = 'string';

            PHP);

        $expected = $this->code(<<<'PHP'

            $x       = 10;
            $bool    = true;
            $another = 'string';

            PHP);
        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testMixedKindVariableAssignmentsAreAlignedSeparately()
    {
        $code = $this->code(<<<'PHP'

            $x = 10;
            $bool = true;
            $array['key_assoc'] = true;
            $this->thing = 'string';
            $this->foo = 'bar';
            SomeClass::$var = true;
            Another::$foo = 'bar';
            $array['key_assoc'] = true;
            $arrayWithKey['another'] = 'aligned';

            PHP);

        $expected = $this->code(<<<'PHP'

            $x    = 10;
            $bool = true;
            $array['key_assoc'] = true;
            $this->thing = 'string';
            $this->foo   = 'bar';
            SomeClass::$var = true;
            Another::$foo   = 'bar';
            $array['key_assoc']      = true;
            $arrayWithKey['another'] = 'aligned';

            PHP);
        $this->assertSame($expected, $this->runner->fix($code));
    }
}
