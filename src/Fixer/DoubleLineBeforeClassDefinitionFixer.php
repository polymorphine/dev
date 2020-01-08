<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Fixer;

use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class DoubleLineBeforeClassDefinitionFixer implements DefinedFixerInterface
{
    public function getName()
    {
        return 'Polymorphine/double_line_before_class_definition';
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([T_TRAIT, T_INTERFACE, T_CLASS]);
    }

    public function getDefinition()
    {
        return new FixerDefinition(
            'There MUST be exactly two blank lines before class, interface or trait definition (or preceding comment).',
            [
                new CodeSample("<?php\nnamespace Foo;\n\nuse Bar;\nuse Baz;\nfinal class Example\n{\n}\n"),
                new CodeSample("<?php\nnamespace Foo;\n\nuse Bar;\nuse Baz;\n\nfinal class Example\n{\n}\n"),
                new CodeSample("<?php\nnamespace Foo;\n\nuse Bar;\nuse Baz;\n\n\n\nfinal class Example\n{\n}\n")
            ]
        );
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return -40;
    }

    public function fix(SplFileInfo $file, Tokens $tokens)
    {
        $definition = $tokens->getNextTokenOfKind(0, [[T_CLASS], [T_INTERFACE], [T_TRAIT]]);
        $idx        = $tokens->getPrevMeaningfulToken($definition);
        if ($tokens[$idx]->isGivenKind([T_FINAL, T_ABSTRACT])) {
            $definition = $idx;
            $idx        = $tokens->getPrevMeaningfulToken($idx);
        }

        $doubleBlank = new Token([T_WHITESPACE, "\n\n\n"]);
        while ($idx++ < $definition) {
            if ($this->hasLineBreak($tokens[$idx])) {
                $tokens[$idx] = $doubleBlank;
                break;
            }
        }

        while ($idx++ < $definition) {
            if ($tokens[$idx]->isWhitespace() && $tokens[$idx]->getContent() !== "\n") {
                $tokens[$idx] = new Token([T_WHITESPACE, "\n"]);
            }
        }
    }

    private function hasLineBreak(Token $token): bool
    {
        return $token->isWhitespace() && strpos($token->getContent(), "\n") !== false;
    }
}
