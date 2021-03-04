<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class DoubleLineBeforeClassDefinitionFixer implements FixerInterface
{
    public function getName(): string
    {
        return 'Polymorphine/double_line_before_class_definition';
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_TRAIT, T_INTERFACE, T_CLASS]);
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

    public function fix(SplFileInfo $file, Tokens $tokens): void
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
