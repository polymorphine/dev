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

use PhpCsFixer\Fixer\FixerInterface;
use Polymorphine\CodeStandards\Tests\Fixtures\TestRunner;


trait FixerTestMethods
{
    private TestRunner $runner;

    private function setRunner(FixerInterface ...$fixers): void
    {
        $this->runner = new TestRunner($fixers);
    }

    private function code(string $code): string
    {
        return '<?php' . PHP_EOL . $code;
    }
}
