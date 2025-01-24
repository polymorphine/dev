<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Sniffer\Sniffs\PhpDoc;

use Polymorphine\Dev\Tests\SnifferTest;
use Polymorphine\Dev\Sniffer\Sniffs\PhpDoc\CallableDefinitionSniff;


class CallableDefinitionSniffTest extends SnifferTest
{
    /** @dataProvider warningsForProperties */
    public function test_CallableParamDoc_WithoutDefinition_GivesWarning(array $warningLines, array $properties)
    {
        $this->assertWarningLines($warningLines, 'PhpDocCallableDefinitions.php', $properties);
    }

    public static function warningsForProperties(): iterable
    {
        return [
            'short+long closure(-)' => [range(15, 18), ['syntax' => 'both', 'includeClosure' => false]],
            'short+long closure(+)' => [range(15, 22), ['syntax' => 'both', 'includeClosure' => true]],
            'short closure (-)'     => [array_merge(range(15, 18), [27, 28, 31]), ['syntax' => 'short', 'includeClosure' => false]],
            'long closure (-)'      => [array_merge(range(15, 18), [23, 24], [33]), ['syntax' => 'long', 'includeClosure' => false]],
            'short closure (+)'     => [array_merge(range(15, 22), range(27, 31)), ['syntax' => 'short', 'includeClosure' => true]],
            'long closure (+)'      => [array_merge(range(15, 26), [32, 33, 34]), ['syntax' => 'long', 'includeClosure' => true]]
        ];
    }

    protected function sniffClass(): string
    {
        return CallableDefinitionSniff::class;
    }
}
