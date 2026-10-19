<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CSFixer;

use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\Token;
use SplFixedArray;


class AlignTokens extends SplFixedArray
{
    private Tokens $tokens;

    /** @param Tokens $tokens */
    public function __construct(Tokens $tokens)
    {
        $this->tokens = $tokens;
        parent::__construct();
    }

    /**
     * @param int $idx
     *
     * @return null|int
     */
    public function prevLineBreak(int $idx): ?int
    {
        return $this->nearestLineBreakIdx($idx, false);
    }

    /**
     * @param int $idx
     *
     * @return null|int
     */
    public function nextLineBreak(int $idx): ?int
    {
        return $this->nearestLineBreakIdx($idx);
    }

    /**
     * @param int  $opIndex
     * @param bool $indent
     *
     * @return int
     */
    public function alignColumn(int $opIndex, bool $indent = false): int
    {
        $lineStart = $this->prevLineBreak($opIndex);
        if (!$lineStart) { return 0; }
        $end  = $indent ? $opIndex - 1 : $this->tokens->getPrevMeaningfulToken($opIndex);
        $code = $this->tokens->generatePartialCode($lineStart, $end);
        return mb_strlen(ltrim($code, "\n")) + ($indent ? 0 : 1);
    }

    /**
     * @param array<int, int> $group
     *
     * @return void
     */
    public function fixGroupIndentation(array $group): void
    {
        $maxLength = max($group);
        foreach ($group as $assign => $length) {
            $this->whitespaceBefore($assign, str_repeat(' ', $maxLength - $length + 1));
        }
    }

    /**
     * @param int $idx    Search start index
     * @param int $offset Relative token distance
     *
     * @return null|int Index of n-th nearest meaningful Token (not whitespace nor comment)
     */
    public function meaningfulToken(int $idx, int $offset): ?int
    {
        $nextIdx = $offset > 0 ? [$this->tokens, 'getNextMeaningfulToken'] : [$this->tokens, 'getPrevMeaningfulToken'];
        $offset  = abs($offset);
        while ($offset-- && $idx) {
            $idx = $nextIdx($idx);
        }
        return $idx;
    }

    /**
     * @param int               $idx    Search start index
     * @param array<int|string> $tokens Token codes or content
     *
     * @return null|int Closest index of found token
     */
    public function prevTokenOfKind(int $idx, array $tokens = []): ?int
    {
        return $this->tokens->getPrevTokenOfKind($idx, $this->wrapTokenIds($tokens));
    }

    /**
     * @param int               $idx    Search start index
     * @param array<int|string> $tokens Token codes or content
     *
     * @return null|int Closest index of found token
     */
    public function nextTokenOfKind(int $idx, array $tokens = []): ?int
    {
        return $this->tokens->getNextTokenOfKind($idx, $this->wrapTokenIds($tokens));
    }

    /**
     * Ensures whitespace before given token.
     *
     * @param int    $idx
     * @param string $whitespace
     *
     * @return bool true if token was added or false if changed
     */
    public function whitespaceBefore(int $idx, string $whitespace = ' '): bool
    {
        return $this->tokens->ensureWhitespaceAtIndex($idx - 1, 1, $whitespace);
    }

    /**
     * Ensures whitespace after given token.
     *
     * @param int    $idx
     * @param string $whitespace
     *
     * @return bool true if token was added or false if changed
     */
    public function whitespaceAfter(int $idx, string $whitespace = ' '): bool
    {
        return $this->tokens->ensureWhitespaceAtIndex($idx + 1, 0, $whitespace);
    }

    /**
     * @param int $idx
     *
     * @return int Index of previous opening array bracket
     */
    public function bracketStart(int $idx): int
    {
        return $this->tokens->findBlockStart(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $idx);
    }

    /**
     * @param int $idx
     *
     * @return int Index of next closing array bracket
     */
    public function bracketEnd(int $idx): int
    {
        return $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $idx);
    }

    /**
     * @param int $index
     *
     * @return Token
     */
    public function offsetGet($index): Token
    {
        return $this->tokens[$index];
    }

    /** @return array<int, Token> */
    public function jsonSerialize(): array
    {
        return $this->tokens->toArray();
    }

    private function nearestLineBreakIdx(int $idx, bool $forwardSearch = true): ?int
    {
        $direction = $forwardSearch ? 1 : -1;
        do {
            $idx = $this->tokens->getTokenOfKindSibling($idx, $direction, [[T_WHITESPACE]]);
        } while ($idx && strpos($this->tokens[$idx]->getContent(), "\n") === false);

        return $idx;
    }

    private function wrapTokenIds(array $tokens): array
    {
        return array_map(fn ($value) => is_int($value) ? [$value] : $value, $tokens);
    }
}
