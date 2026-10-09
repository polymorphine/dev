<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CSFixer\Tokens;

use PhpCsFixer\Tokenizer\Tokens;


trait FixerMethods
{
    private Tokens $tokens;

    private function isNextLine(int $idx): bool
    {
        return substr_count($this->tokens[$idx]->getContent(), "\n") === 1;
    }

    private function nextLineBreak(int $idx): ?int
    {
        return $this->nearestLineBreakIdx($idx);
    }

    private function prevLineBreak(int $idx): ?int
    {
        return $this->nearestLineBreakIdx($idx, false);
    }

    private function nearestLineBreakIdx(int $idx, bool $forwardSearch = true): ?int
    {
        $direction = $forwardSearch ? 1 : -1;
        do {
            $idx = $this->tokens->getTokenOfKindSibling($idx, $direction, [[T_WHITESPACE]]);
        } while ($idx && strpos($this->tokens[$idx]->getContent(), "\n") === false);

        return $idx;
    }

    private function indentationPointLength(int $newLine, int $operator, bool $align = true): int
    {
        $end  = $align ? $this->tokens->getPrevMeaningfulToken($operator) : $operator - 1;
        $code = $this->tokens->generatePartialCode($newLine, $end);
        return $this->codeLength($code) + ($align ? 1 : 0);
    }

    private function codeLength(string $code): int
    {
        return mb_strlen(ltrim($code, "\n"));
    }

    private function fixGroupIndentation(array $group): void
    {
        $maxLength = max($group);
        foreach ($group as $assign => $length) {
            $this->tokens->ensureWhitespaceAtIndex($assign - 1, 1, str_repeat(' ', $maxLength - $length + 1));
        }
    }

    private function meaningfulToken(int $idx, int $offset): ?int
    {
        $nextIdx = $offset > 0 ? [$this->tokens, 'getNextMeaningfulToken'] : [$this->tokens, 'getPrevMeaningfulToken'];
        $offset  = abs($offset);
        while ($offset-- && $idx) {
            $idx = $nextIdx($idx);
        }
        return $idx;
    }
}
