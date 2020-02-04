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
use PhpCsFixer\Tokenizer\CT;


final class ArrayContext
{
    use FixerMethods;

    private $tokens;
    private $start;
    private $end;

    /**
     * @param Tokens $tokens
     * @param int    $start  Opening brace index of array
     */
    public function __construct(Tokens $tokens, int $start)
    {
        $this->tokens = $tokens;
        $this->start  = $start;
    }

    /**
     * @return int Index of the closing brace
     */
    public function lastTokenIdx(): int
    {
        return $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $this->start);
    }

    /**
     * Aligns associative indentation for array and its sub arrays.
     */
    public function alignIndentation(): void
    {
        $tokenTypes = [[CT::T_ARRAY_SQUARE_BRACE_OPEN], [T_DOUBLE_ARROW], [CT::T_ARRAY_SQUARE_BRACE_CLOSE]];
        $group      = [];
        $idx        = $this->start;
        while ($idx = $this->tokens->getNextTokenOfKind($idx, $tokenTypes)) {
            $type = $this->tokens[$idx]->getId();
            if ($type === CT::T_ARRAY_SQUARE_BRACE_CLOSE) { break; }
            if ($type === CT::T_ARRAY_SQUARE_BRACE_OPEN) {
                $array = new self($this->tokens, $idx);
                $array->alignIndentation();
                $idx = $array->lastTokenIdx();
                continue;
            }
            $lineEnd = $this->nextLineBreak($idx);
            if ($this->isMultipleAssign($idx, $lineEnd)) { return; }
            if ($this->isMultilineValue($lineEnd)) { continue; }

            $lineStart = $this->prevLineBreak($idx);
            $group[] = [$idx, $this->indentationPointLength($lineStart, $idx)];
        }
        if (count($group) < 2) { return; }
        $this->fixGroupIndentation($group);
    }

    private function isMultilineValue(int $lineEnd): bool
    {
        $isComma = $this->tokens[$lineEnd - 1]->getContent() === ',';
        return !$isComma && !$this->tokens[$lineEnd + 1]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_CLOSE);
    }

    private function isMultipleAssign(int $firstArrow, int $lineEnd): int
    {
        $last = $this->tokens->getPrevTokenOfKind($lineEnd, [[T_DOUBLE_ARROW], [CT::T_ARRAY_SQUARE_BRACE_CLOSE]]);
        if ($this->tokens[$last]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_CLOSE)) {
            $continueFrom = $this->tokens->findBlockStart(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $last);
            return $continueFrom < $firstArrow ? true : $this->isMultipleAssign($firstArrow, $continueFrom);
        }

        return $firstArrow !== $last;
    }
}
