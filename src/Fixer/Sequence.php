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

use PhpCsFixer\Tokenizer\Tokens;


final class Sequence
{
    use FixerMethods;

    public int   $idx;
    public array $tokenIds;

    /**
     * @param Tokens $tokens
     * @param int    $idx      First element in the sequence
     * @param array  $tokenIds Ordered list of meaningful tokens
     */
    public function __construct(Tokens $tokens, int $idx, array $tokenIds = [])
    {
        $this->tokens   = $tokens;
        $this->idx      = $idx;
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
        $idx = $this->prevLineBreak($sequence->idx);
        if (!$this->isNextLine($idx)) { return false; }
        if ($idx !== $this->nextLineBreak($this->idx)) { return false; }

        return $this->match($sequence);
    }

    /**
     * Compares two sequences for same meaningful tokens.
     *
     * @param Sequence $sequence
     *
     * @return bool
     */
    public function match(Sequence $sequence): bool
    {
        return $this->tokenIds === $sequence->tokenIds;
    }
}
