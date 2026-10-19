<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CSFixer\Fixer\SequenceAlignFixers;

use Polymorphine\Dev\CSFixer\AlignTokens;


final class Sequence
{
    public int $endIdx;

    private AlignTokens $tokens;
    private array       $tokenIds;

    /**
     * Token sequence from line start until $endIdx.
     *
     * @param AlignTokens $tokens
     * @param int         $endIdx
     * @param list<int>   $tokenIds
     */
    public function __construct(AlignTokens $tokens, int $endIdx, array $tokenIds = [])
    {
        $this->tokens   = $tokens;
        $this->endIdx   = $endIdx;
        $this->tokenIds = $tokenIds;
    }

    /**
     * Compares two sequences for consecutive lines and
     * same meaningful tokens.
     *
     * @param Sequence $sequence
     *
     * @return bool
     */
    public function sameGroup(Sequence $sequence): bool
    {
        if (!$this->tokenIds || !$sequence->tokenIds) { return false; }
        $idx = $this->tokens->prevLineBreak($sequence->endIdx);
        return $this->isNextLine($idx)
            && $this->tokens->nextLineBreak($this->endIdx) === $idx
            && $this->match($sequence);
    }

    /**
     * @return int Code line length up to sequence ending token
     */
    public function length(): int
    {
        return $this->tokens->alignColumn($this->endIdx - 1);
    }

    private function isNextLine(int $idx): bool
    {
        return substr_count($this->tokens[$idx]->getContent(), "\n") === 1;
    }

    private function match(Sequence $sequence): bool
    {
        return $this->tokenIds === $sequence->tokenIds;
    }
}
