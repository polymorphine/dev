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


final class ArrayContext
{
    use FixerMethods;

    const OPEN_ARRAY     = 10004;
    const CLOSE_ARRAY    = 10003;
    const ARROW_OPERATOR = T_DOUBLE_ARROW;

    private $idx;
    private $tokens;

    private $groups = [];
    private $end;

    public function __construct(int $start, Tokens $tokens)
    {
        $this->idx    = $start;
        $this->tokens = $tokens;
    }

    public function idx()
    {
        return $this->end ?: $this->end = $this->findLast($this->idx);
    }

    public function operatorGroups(): array
    {
        if ($this->idx && !$this->isMultiline($this->idx)) {
            $this->end = $this->findLast($this->idx);
            return [];
        }

        $tokenTypes = [[self::OPEN_ARRAY], [self::CLOSE_ARRAY], [self::ARROW_OPERATOR]];
        $group      = [];
        while ($this->idx = $this->tokens->getNextTokenOfKind($this->idx, $tokenTypes)) {
            if (!$this->idx) { break; }

            switch ($this->tokens[$this->idx]->getId()) {
                case self::OPEN_ARRAY:
                    $this->getNestedGroups();
                    break;
                case self::ARROW_OPERATOR:
                    if ($this->multilineAssign()) { break; }
                    $newLine = $this->prevLineBreak($this->idx);
                    $group[] = [$this->idx, $this->indentationPointLength($newLine, $this->idx)];
                    $this->idx = $this->nextLineBreak($this->idx);
                    break;
                case self::CLOSE_ARRAY:
                    $this->end = $this->idx;
                    return $this->groupsWithLocalGroup($group);
            }
        }
        return $this->groups;
    }

    private function getNestedGroups()
    {
        $array = new ArrayContext($this->idx, $this->tokens);
        $this->mergeGroups($array->operatorGroups());
        $this->idx = $array->idx();
    }

    private function groupsWithLocalGroup(array $group): array
    {
        if (!$group) { return $this->groups; }
        $this->groups[] = $group;
        return $this->groups;
    }

    private function mergeGroups(array $groups)
    {
        foreach ($groups as $group) {
            $this->groups[] = $group;
        }
    }

    private function isMultiline(int $idx): bool
    {
        $arrayEnd = $this->findLast($idx);
        $nested   = $this->findNested($idx, $arrayEnd);
        $idx      = $this->tokens->getNextTokenOfKind($idx, [[self::ARROW_OPERATOR]]);
        if (!$idx) { return false; }

        while ($idx < $arrayEnd) {
            $lineEnd = $this->nextLineBreak($idx);
            $idx     = $this->tokens->getNextTokenOfKind($idx, [[self::ARROW_OPERATOR]]);
            if (!$idx) { break; }
            if ($nested && $nested < min($lineEnd, $idx)) {
                $idx    = $this->findLast($nested);
                $nested = $this->findNested($idx, $arrayEnd);
                $idx    = $this->tokens->getNextTokenOfKind($idx, [[self::ARROW_OPERATOR]]);
                if (!$idx) { break; }
                continue;
            }

            if ($idx < $lineEnd) { return false; }
        }

        return true;
    }

    private function multilineAssign()
    {
        $lineEnd = $this->nextLineBreak($this->idx);
        $comma   = $this->tokens[$lineEnd - 1]->getContent() === ',';
        return !$comma && !$this->tokens[$lineEnd + 1]->isGivenKind(self::CLOSE_ARRAY);
    }

    private function findLast(int $idx)
    {
        return $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $idx);
    }

    private function findNested(int $idx, int $maxIdx): ?int
    {
        $nested = $this->tokens->getNextTokenOfKind($idx, [[self::OPEN_ARRAY]]);
        return $nested > $maxIdx ? null : $nested;
    }
}
