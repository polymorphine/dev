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


/**
 * Fixes required double pass for both multiline argument fix and
 * same line brace fix caused by higher priority of BracesFixer
 * ignoring later MethodArgumentSpaceFixer changes.
 */
class BraceAfterMultilineParamMethodFixer implements FixerInterface
{
    use FixerMethods;

    public function getName(): string
    {
        return 'Polymorphine/brace_after_multiline_param_method';
    }

    public function isCandidate(Tokens $tokens): bool
    {
        $classConstruct = $tokens->isAnyTokenKindsFound([T_CLASS, T_TRAIT]);
        return $classConstruct && $tokens->isTokenKindFound(T_FUNCTION);
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
        $this->tokens = $tokens;

        $idx = $tokens->getNextTokenOfKind(0, [[T_CLASS], [T_TRAIT]]);
        while ($idx = $tokens->getNextTokenOfKind($idx, [[T_FUNCTION]])) {
            $endParams = $tokens->getNextTokenOfKind($idx, [')']);
            $lineBreak = $this->nextLineBreak($idx) ?: $endParams;
            if ($endParams <= $lineBreak) { continue; }

            $braceIdx = $tokens->getNextTokenOfKind($endParams, ['{']);
            if (!$braceIdx) { break; }

            $semicolon = $tokens->getNextTokenOfKind($endParams, [';']) ?: $braceIdx;
            if ($braceIdx >= $semicolon) { continue; }

            $spaceIdx   = $braceIdx - 1;
            $spaceToken = new Token([T_WHITESPACE, ' ']);
            if ($tokens[$spaceIdx]->getContent() === ' ') { continue; }
            if ($tokens[$spaceIdx]->isWhitespace()) {
                $tokens[$spaceIdx] = $spaceToken;
            } else {
                $tokens->insertAt($braceIdx, $spaceToken);
            }
        }
    }
}
