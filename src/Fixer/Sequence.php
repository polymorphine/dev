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

use PhpCsFixer\Tokenizer\Tokens;


final class Sequence
{
    use FixerMethods;

    public int   $idx;
    public array $tokenIds;

    public function __construct(Tokens $tokens, int $idx, array $tokenIds = [])
    {
        $this->tokens   = $tokens;
        $this->idx      = $idx;
        $this->tokenIds = $tokenIds;
    }

    public function sameGroup(Sequence $sequence): bool
    {
        $idx = $this->prevLineBreak($sequence->idx);
        if (!$this->isNextLine($idx)) { return false; }
        if ($idx !== $this->nextLineBreak($this->idx)) { return false; }

        return $this->match($sequence);
    }

    public function match(Sequence $sequence): bool
    {
        return $this->tokenIds === $sequence->tokenIds;
    }
}
