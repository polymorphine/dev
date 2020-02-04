<?php

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


final class BraceAfterFunctionFixer implements FixerInterface
{
    public function getName()
    {
        return 'Polymorphine/brace_after_method';
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_FUNCTION);
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
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_FUNCTION)) { continue; }

            $braceIdx      = $tokens->getNextTokenOfKind($index, ['{']);
            $definitionEnd = $tokens->getPrevMeaningfulToken($braceIdx);

            $spaceToken = new Token([T_WHITESPACE, ' ']);
            if ($braceIdx - $definitionEnd === 1) {
                $tokens->insertAt($braceIdx, $spaceToken);
                continue;
            }

            $tokens[$definitionEnd + 1] = $spaceToken;
        }
    }
}
