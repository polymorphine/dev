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

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class AlignedTypedPropertiesFixer implements FixerInterface
{
    use FixerMethods;

    public function getName()
    {
        return 'Polymorphine/aligned_properties';
    }

    public function isRisky()
    {
        return false;
    }

    public function getPriority()
    {
        return -39;
    }

    public function supports(SplFileInfo $file)
    {
        return true;
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound([T_CLASS, T_TRAIT]);
    }

    public function fix(SplFileInfo $file, Tokens $tokens)
    {
        $this->tokens = $tokens;

        $idx    = $this->tokens->getNextTokenOfKind(0, [[T_CLASS], [T_TRAIT]]);
        $groups = $this->findGroups($this->tokens->getNextTokenOfKind($idx, ['{']));
        foreach ($groups as $group) {
            $this->fixGroupIndentation($group);
        }
    }

    private function findGroups($idx): array
    {
        $groups = [];
        $group  = [];
        $prev   = new Sequence($this->tokens, $idx);
        while ($next = $this->nextSequence($prev->idx)) {
            if (!$prev->sameGroup($next)) {
                if (count($group) > 1) {
                    $groups[] = $group;
                }
                $group = [];
            }

            $group[] = $this->alignIndex($next->idx);
            $prev = $next;
        }

        if (count($group) > 1) {
            $groups[] = $group;
        }

        return $groups;
    }

    private function nextSequence(int $idx): ?Sequence
    {
        $idx = $this->tokens->getNextTokenOfKind($idx, [[T_PRIVATE], [T_PROTECTED], [T_PUBLIC]]);
        if (!$idx) { return null; }
        $end = $this->tokens->getNextTokenOfKind($idx, [[T_VARIABLE], [T_FUNCTION], [T_CONST]]);
        if (!$this->tokens[$end]->isGivenKind(T_VARIABLE)) {
            return $this->nextSequence($end);
        }

        $tokenIds[] = $this->tokens[$idx]->getId();
        while ($idx = $this->tokens->getNextMeaningfulToken($idx)) {
            if ($idx === $end) { return $this->nextSequence($end); }
            if (!$this->tokens[$idx]->isGivenKind(T_STATIC)) { break; }
            $tokenIds[] = $this->tokens[$idx]->getId();
        }

        $tokenIds[] = T_STRING;
        return new Sequence($this->tokens, $end, $tokenIds);
    }

    private function alignIndex(int $idx): array
    {
        $start = $this->tokens->getPrevTokenOfKind($idx, [[T_PRIVATE], [T_PROTECTED], [T_PUBLIC]]);
        return [$idx, $this->indentationPointLength($start + 2, $idx - 1)];
    }
}
