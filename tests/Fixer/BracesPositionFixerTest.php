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

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\WhitespacesFixerConfig;
use Polymorphine\Dev\Tests\FixerTest;
use Polymorphine\Dev\Fixer\BracesPositionFixer;


/**
 * Based on refactored tests from original PhpCSFixer.
 *
 * @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.95.27/tests/Fixer/Basic/BracesPositionFixerTest.php
 */
class BracesPositionFixerTest extends FixerTest
{
    /**
     * @dataProvider codeSamplesWithBraces
     */
    public function test_BracesPositionForControlStructures_AreFixed(string $expected, ?string $input = null): void
    {
        $input ? $this->assertFixed($input, $expected) : $this->assertUnchanged($expected);
    }

    /** @return iterable<string, list<string>> */
    public static function codeSamplesWithBraces(): iterable
    {
        yield 'if' => [<<<'PHP'
            <?php
            if ($foo) {
                foo();
            }
            PHP, <<<'PHP'
            <?php
            if ($foo)
            {
                foo();
            }
            PHP];

        yield 'else' => [<<<'PHP'
            <?php
            if ($foo) {
                foo();
            } else {
                bar();
            }
            PHP, <<<'PHP'
            <?php
            if ($foo)
            {
                foo();
            }
            else
            {
                bar();
            }
            PHP];

        yield 'elseif' => [<<<'PHP'
            <?php
            if ($foo) {
                foo();
            } elseif ($bar) {
                bar();
            }
            PHP, <<<'PHP'
            <?php
            if ($foo)
            {
                foo();
            }
            elseif ($bar)
            {
                bar();
            }
            PHP];

        yield 'else if' => [<<<'PHP'
            <?php
            if ($foo) {
                foo();
            } else if ($bar) {
                bar();
            }
            PHP, <<<'PHP'
            <?php
            if ($foo)
            {
                foo();
            }
            else if ($bar)
            {
                bar();
            }
            PHP];

        yield 'for' => [<<<'PHP'
            <?php
                for (;;) {
                    foo();
                }
            PHP, <<<'PHP'
            <?php
                for (;;)
                {
                    foo();
                }
            PHP];

        yield 'foreach' => [<<<'PHP'
            <?php
                foreach ($foo as $bar) {
                    foo();
                }
            PHP, <<<'PHP'
            <?php
                foreach ($foo as $bar)
                {
                    foo();
                }
            PHP];

        yield 'while' => [<<<'PHP'
            <?php
            while ($foo) {
                foo();
            }
            PHP, <<<'PHP'
            <?php
            while ($foo)
            {
                foo();
            }
            PHP];

        yield 'do while' => [<<<'PHP'
            <?php
            do {
                foo();
            } while ($foo);
            PHP, <<<'PHP'
            <?php
            do
            {
                foo();
            }
            while ($foo);
            PHP];

        yield 'switch' => [<<<'PHP'
            <?php
            switch ($foo) {
                case 1:
                    foo();
            }
            PHP, <<<'PHP'
            <?php
            switch ($foo)
            {
                case 1:
                    foo();
            }
            PHP];

        yield 'try catch finally' => [<<<'PHP'
            <?php
            try {
                foo();
            } catch (Exception $e) {
                bar();
            } finally {
                baz();
            }
            PHP, <<<'PHP'
            <?php
            try
            {
                foo();
            }
            catch (Exception $e)
            {
                bar();
            }
            finally
            {
                baz();
            }
            PHP];

        yield 'class' => [<<<'PHP'
            <?php
            class Foo
            {
            }
            PHP, <<<'PHP'
            <?php
            class Foo {
            }
            PHP];

        yield 'first line class' => [<<<'PHP'
            <?php
            class Foo
            {
            }
            PHP, <<<'PHP'
            <?php
            class Foo {
            }
            PHP];

        yield 'function' => [<<<'PHP'
            <?php
            function foo()
            {
            }
            PHP, <<<'PHP'
            <?php
            function foo() {
            }
            PHP];

        yield 'anonymous function' => [<<<'PHP'
            <?php
            $foo = function () {
            };
            PHP, <<<'PHP'
            <?php
            $foo = function ()
            {
            };
            PHP];

        yield 'with blank lines inside braces' => [<<<'PHP'
            <?php
            class Foo
            {
            
                public function foo()
                {
            
                    if (true) {
            
                        echo "foo";
            
                    }
            
                }
            
            }
            PHP, <<<'PHP'
            <?php
            class Foo {
            
                public function foo() {
            
                    if (true)
                    {
            
                        echo "foo";
            
                    }
            
                }
            
            }
            PHP];

        yield 'with comment after opening brace' => [<<<'PHP'
            <?php
            function foo() /* foo */ // foo
            {
            }
            PHP, <<<'PHP'
            <?php
            function foo() /* foo */ { // foo
            }
            PHP];

        yield 'next line with multiline signature' => [<<<'PHP'
            <?php
            if (
                $foo
            ) {
                foo();
            }
            PHP, <<<'PHP'
            <?php
            if (
                $foo
            )
            {
                foo();
            }
            PHP];

        yield 'next line with newline before closing parenthesis' => [<<<'PHP'
            <?php
                if ($foo
                ) {
                    foo();
                }
            PHP, <<<'PHP'
            <?php
                if ($foo
                )
                {
                    foo();
                }
            PHP];

        yield 'anonymous class' => [<<<'PHP'
            <?php
            $foo = new class() {
            };
            PHP, <<<'PHP'
            <?php
            $foo = new class()
            {
            };
            PHP];

        yield 'next line with multiline signature and return type' => [<<<'PHP'
            <?php
            function foo(
                $foo
            ): int {
                fooz();
            }
            PHP, <<<'PHP'
            <?php
            function foo(
                $foo
            ): int
            {
                fooz();
            }
            PHP];

        yield 'next line with multiline signature and return type (nullable)' => [<<<'PHP'
            <?php
            function foo(
                $foo
            ): ?int {
                fooz();
            }
            PHP, <<<'PHP'
            <?php
            function foo(
                $foo
            ): ?int
            {
                fooz();
            }
            PHP];

        yield 'next line with multiline signature and return type (array)' => [<<<'PHP'
            <?php
            function foo(
                $foo
            ): array {
                fooz();
            }
            PHP, <<<'PHP'
            <?php
            function foo(
                $foo
            ): array
            {
                fooz();
            }
            PHP];

        yield 'next line with multiline signature and return type (class name)' => [<<<'PHP'
            <?php
            function foo(
                $foo
            ): \Foo\Bar {
                fooz();
            }
            PHP, <<<'PHP'
            <?php
            function foo(
                $foo
            ): \Foo\Bar
            {
                fooz();
            }
            PHP];

        yield 'next line with newline before closing parenthesis and return type' => [<<<'PHP'
            <?php
            function foo($foo
            ): int {
                fooz();
            }
            PHP, <<<'PHP'
            <?php
            function foo($foo
            ): int
            {
                fooz();
            }
            PHP];

        yield 'next line with newline before closing parenthesis and callable type' => [<<<'PHP'
            <?php
            function foo($foo
            ): callable {
                return function (): void {};
            }
            PHP, <<<'PHP'
            <?php
            function foo($foo
            ): callable
            {
                return function (): void {};
            }
            PHP];

        yield 'next line with newline in signature but not before closing parenthesis and return type' => [<<<'PHP'
            <?php
            function foo(
                $foo): int
            {
                fooz();
            }
            PHP, <<<'PHP'
            <?php
            function foo(
                $foo): int {
                fooz();
            }
            PHP];

        yield 'multiple elseifs' => [<<<'PHP'
            <?php if ($foo) {
            } elseif ($foo) {
            } elseif ($foo) {
            } elseif ($foo) {
            } elseif ($foo) {
            } elseif ($foo) {
            } elseif ($foo) {
            } elseif ($foo) {
            }
            PHP, <<<'PHP'
            <?php if ($foo){
            } elseif ($foo){
            } elseif ($foo)
            {
            } elseif ($foo){
            } elseif ($foo){
            } elseif ($foo)
            {
            }
            elseif ($foo)   {
            } elseif ($foo){
            }
            PHP];

        yield 'open brace preceded by comment and whitespace' => [<<<'PHP'
            <?php
            if (true) { /* foo */
                foo();
            }
            PHP, <<<'PHP'
            <?php
            if (true) /* foo */ {
                foo();
            }
            PHP];

        yield 'open brace surrounded by comment and whitespace' => [<<<'PHP'
            <?php
            if (true) { /* foo */ /* bar */
                foo();
            }
            PHP, <<<'PHP'
            <?php
            if (true) /* foo */ { /* bar */
                foo();
            }
            PHP];

        yield 'open brace not preceded by space and followed by a comment' => [<<<'PHP'
            <?php class test
            {
                public function example()// example
                {
                }
            }
            
            PHP, <<<'PHP'
            <?php class test
            {
                public function example(){// example
                }
            }
            
            PHP];

        yield 'open brace not preceded by space and followed by a space and comment' => [<<<'PHP'
            <?php class test
            {
                public function example() // example
                {
                }
            }
            
            PHP, <<<'PHP'
            <?php class test
            {
                public function example(){ // example
                }
            }
            
            PHP];

        yield 'if with comment after brace' => [<<<'PHP'
            <?php
            if ($x) {//

                f();
            }
            PHP, <<<'PHP'
            <?php
            if ($x)//
            {

                f();
            }
            PHP];

        yield 'variable terminated by close tag' => [<<<'PHP'
            <?php

            $pager = new class {
            public function bar()
            {
            return 'baz';
            }
            };

            echo $pager->bar()
            ?>
            PHP, <<<'PHP'
            <?php

            $pager = new class { public function bar() { return 'baz'; } };

            echo $pager->bar()
            ?>
            PHP];

        yield 'confirm {$foo} does not cause harm' => [<<<'PHP'
            <?php
            $foo = "foo";
            if($foo) {
                echo "
            /* some sql */ {$foo} /* some sql */;
                ";
            }
            PHP];

        yield 'newline after opening brace' => [<<<'PHP'
            <?php if (true) {
            }
            PHP, <<<'PHP'
            <?php if (true) {}
            PHP];

        yield 'newline before closing comment and parenthesis' => [<<<'PHP'
            <?php
            function foo($foo
            /* comment */) {
            }
            PHP];

        yield 'anonymous function single line (spacing)' => [<<<'PHP'
            <?php $f = function() {};
            PHP, <<<'PHP'
            <?php $f = function(){};
            PHP];

        yield 'comment between closing parenthesis and opening brace' => [<<<'PHP'
            <?php if ($a) { /* comment */
            }
            PHP, <<<'PHP'
            <?php if ($a) /* comment */ {}
            PHP];
    }

    protected function fixer(): FixerInterface
    {
        $fixer = new BracesPositionFixer();
        $fixer->setWhitespacesConfig(new WhitespacesFixerConfig());
        return $fixer;
    }

    protected function properties(): array
    {
        return ['name' => 'Polymorphine/braces_position', 'priority' => -2];
    }
}
