<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\CSFixer\Fixer;

use Polymorphine\Dev\Tests\CSFixer\FixerTest;
use Polymorphine\Dev\CSFixer\Fixer\OpeningTagLineSpacingFixer;


class OpeningTagLineSpacingFixerTest extends FixerTest
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
            <?php echo 'First line, huh?';
            
            /* Comment */
            declare(strict_types=1);
            
            echo 'declare';
            
            CODE;

        $expected = <<<'CODE'
            <?php declare(strict_types=1);
            
            echo 'First line, huh?';
            
            /* Comment */
            echo 'declare';
            
            CODE;

        $this->assertFixed($code, $expected);
    }

    protected function fixer(): OpeningTagLineSpacingFixer
    {
        return new OpeningTagLineSpacingFixer();
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/opening_tag_line_spacing', 'priority' => -39];
    }
}
