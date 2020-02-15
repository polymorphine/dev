<?php

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
    public function testFileWithoutDeclareIsUnchanged()
    {
        $code = <<<'CODE'
            <?php
            
            echo 'declare';
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testFileWithDeclareInFirstLineIsUnchanged()
    {
        $code = <<<'CODE'
            <?php declare(strict_types=1);
            
            echo 'strict_types=1';
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testFileWithDifferentDeclareIsUnchanged()
    {
        $code = <<<'CODE'
            <?php
            
            declare(ticks=1);
            echo 'strict_types=1';
            
            CODE;

        $this->assertSame($code, $this->runner->fix($code));
    }

    public function testDeclareNotInFirstLineIsMoved()
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

        $this->assertSame($expected, $this->runner->fix($code));
    }

    public function testDeclareNotInFirstLineIsMovedWIthFollowingWhitespace()
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

        $this->assertSame($expected, $this->runner->fix($code));
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
