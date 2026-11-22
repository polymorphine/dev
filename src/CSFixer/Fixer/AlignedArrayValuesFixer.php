<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CSFixer\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use Polymorphine\Dev\CSFixer\AlignTokens;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\CT;
use SplFileInfo;
use Generator;


final class AlignedArrayValuesFixer implements FixerInterface
{
    private const BRACKET_TOKENS = [CT::T_ARRAY_BRACKET_OPEN, T_DOUBLE_ARROW, CT::T_ARRAY_BRACKET_CLOSE];

    private AlignTokens $tokens;

    public function getName(): string
    {
        return 'Polymorphine/aligned_array_values';
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition('Aligns arrow operator in multiline arrays.', []);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOUBLE_ARROW);
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
        $this->tokens = new AlignTokens($tokens);
        foreach ($this->groups() as $group) {
            count($group) > 1 && $this->tokens->align($group);
        }
    }

    private function groups(): Generator
    {
        $stack = [];
        $group = [];
        while ($idx = $this->tokens->nextTokenOfKind($idx ?? 0, self::BRACKET_TOKENS)) {
            $type = $this->tokens[$idx]->getId();
            if ($type === CT::T_ARRAY_BRACKET_CLOSE) {
                yield $group;
                $group = array_pop($stack) ?? [];
            } elseif ($type === CT::T_ARRAY_BRACKET_OPEN) {
                $stack[] = $group;
                $group = [];
            } elseif ($this->isMultipleAssign($idx, $this->tokens->nextLineBreak($idx))) {
                $idx   = $this->fixLocalGroupOperators(array_key_first($group) ?? $idx);
                $group = array_pop($stack) ?? [];
            } else {
                !$this->tokens[$idx - 1]->isWhitespace() && $this->tokens->whitespaceBefore($idx) && $idx++;
                $this->tokens->whitespaceAfter($idx);
                $group[$idx] = $this->tokens->alignColumn($idx);
            }
        }
    }

    private function isMultipleAssign(int $firstArrow, int $lineEnd): bool
    {
        $last = $this->tokens->prevTokenOfKind($lineEnd, [T_DOUBLE_ARROW, CT::T_ARRAY_BRACKET_CLOSE]);
        if ($this->tokens[$last]->isGivenKind(CT::T_ARRAY_BRACKET_CLOSE)) {
            $continueFrom = $this->tokens->bracketStart($last);
            return $continueFrom < $firstArrow || $this->isMultipleAssign($firstArrow, $continueFrom);
        }
        return $firstArrow !== $last;
    }

    private function fixLocalGroupOperators(int $idx): int
    {
        $end = $this->tokens->bracketEnd($this->tokens->prevTokenOfKind($idx, [CT::T_ARRAY_BRACKET_OPEN]));
        while ($idx < $end) {
            $this->tokens->whitespaceAfter($idx) && $end++;
            $this->tokens->whitespaceBefore($idx) && $end++;
            $idx = $this->tokens->nextTokenOfKind($idx + 1, [T_DOUBLE_ARROW]) ?? $end;
        }
        return $end;
    }
}
