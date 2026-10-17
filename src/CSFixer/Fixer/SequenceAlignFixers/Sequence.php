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

use Polymorphine\Dev\CSFixer\Tokens\FixerMethods;
use PhpCsFixer\Tokenizer\Tokens;


final class Sequence
{
    use FixerMethods;

    public int $endIdx;

    private array $tokenIds;

    /**
     * Token sequence from line start until $endIdx.
     *
     * @param Tokens    $tokens
     * @param int       $endIdx
     * @param list<int> $tokenIds
     */
    public function __construct(Tokens $tokens, int $endIdx, array $tokenIds = [])
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
        $idx = $this->prevLineBreak($sequence->endIdx);
        return $this->isNextLine($idx) && $this->nextLineBreak($this->endIdx) === $idx && $this->match($sequence);
    }

    /**
     * @return int Code line length up to sequence ending token
     */
    public function length(): int
    {
        $start = $this->prevLineBreak($this->endIdx);
        return $start ? $this->indentationPointLength($start, $this->endIdx - 1) : 0;
    }

    /**
     * Compares two sequences for same meaningful tokens.
     *
     * @param Sequence $sequence
     *
     * @return bool
     */
    private function match(Sequence $sequence): bool
    {
        return $this->tokenIds === $sequence->tokenIds;
    }
}
