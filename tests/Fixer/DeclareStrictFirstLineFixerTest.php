<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Fixer;

use Polymorphine\Dev\Tests\FixerTest;
use Polymorphine\Dev\Fixer\DeclareStrictFirstLineFixer;
use PhpCsFixer\Fixer\FixerInterface;


class DeclareStrictFirstLineFixerTest extends FixerTest
{
    public function test_FileWithoutDeclare_IsUnchanged()
    {
        $code = <<<'CODE'
            <?php
            
            echo 'declare';
            
            CODE;

        $this->assertUnchanged($code);
    }

    public function test_FileWithDeclareInFirstLine_IsUnchanged()
    {
        $code = <<<'CODE'
            <?php declare(strict_types=1);
            
            echo 'strict_types=1';
            
            CODE;

        $this->assertUnchanged($code);
    }

    public function test_FileWithDifferentDeclare_IsUnchanged()
    {
        $code = <<<'CODE'
            <?php
            
            declare(ticks=1);
            echo 'strict_types=1';
            
            CODE;

        $this->assertUnchanged($code);
    }

    public function test_DeclareNotInFirstLine_IsMoved()
    {
        $code = <<<'CODE'
            <?php
            
            declare(strict_types=1);
            echo 'declare';
            
            CODE;

        $expected = <<<'CODE'
            <?php declare(strict_types=1);
            
            echo 'declare';
            
            CODE;

        $this->assertFixed($code, $expected);
    }

    public function test_DeclareNotInFirstLine_IsMovedWIthFollowingWhitespace()
    {
        $code = <<<'CODE'
            <?php
            
            /* Comment */
            declare(strict_types=1);
            
            echo 'declare';
            
            CODE;

        $expected = <<<'CODE'
            <?php declare(strict_types=1);
            
            /* Comment */
            echo 'declare';
            
            CODE;

        $this->assertFixed($code, $expected);
    }

    protected function fixer(): FixerInterface
    {
        return new DeclareStrictFirstLineFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/declare_strict_first_line', 'priority' => -39];
    }
}
