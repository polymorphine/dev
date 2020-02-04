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
use PhpCsFixer\Tokenizer\CT;
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

        $definition = [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC];
        $typed      = false;
        $tokenIds   = [];
        while ($idx < $end) {
            $tokenId = $this->tokens[$idx]->getId();
            if (!in_array($tokenId, $definition, true)) {
                $typed   = true;
                $tokenId = T_STRING;
            }
            $tokenIds[] = $tokenId;
            do {
                $idx = $this->tokens->getNextMeaningfulToken($idx);
            } while ($this->isNullableToken($idx));
        }

        return $typed ? new Sequence($this->tokens, $end, $tokenIds) : $this->nextSequence($end);
    }

    private function alignIndex(int $idx): array
    {
        $nullableChars = $this->isNullableToken($idx - 3) ? 1 : 0;
        return [$idx, strlen($this->tokens[$idx - 2]->getContent()) + $nullableChars];
    }

    private function isNullableToken(int $idx): bool
    {
        return $this->tokens[$idx]->getId() === CT::T_NULLABLE_TYPE;
    }
}
