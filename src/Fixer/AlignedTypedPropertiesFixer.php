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

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


class AlignedTypedPropertiesFixer implements FixerInterface
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
        return -40;
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

        $idx       = $this->tokens->getNextTokenOfKind(0, [[T_CLASS], [T_TRAIT]]);
        $classBody = $this->tokens->getNextTokenOfKind($idx, ['{']);

        $idx      = $this->tokens->getNextTokenOfKind($classBody, [[T_FUNCTION]]);
        $maxRange = $this->tokens->getPrevTokenOfKind($idx, [[T_PRIVATE], [T_PROTECTED], [T_PUBLIC]]);

        $sequences = [
            [[T_PRIVATE], [T_STRING], [T_VARIABLE]],
            [[T_PROTECTED], [T_STRING], [T_VARIABLE]],
            [[T_PUBLIC], [T_STRING], [T_VARIABLE]],
            [[T_PRIVATE], [T_STATIC], [T_STRING], [T_VARIABLE]],
            [[T_PROTECTED], [T_STATIC], [T_STRING], [T_VARIABLE]],
            [[T_PUBLIC], [T_STATIC], [T_STRING], [T_VARIABLE]]
        ];

        foreach ($sequences as $sequence) {
            $groups = $this->findGroups($sequence, $classBody, $maxRange);
            foreach ($groups as $group) {
                $this->fixGroupIndentation($group);
            }
        }
    }

    private function findGroups(array $sequence, $idx, $maxRange): array
    {
        $groups = [];
        $group  = [];
        while ($next = $this->nextSequence($idx, $sequence, $maxRange)) {
            $newGroup = $group && ($next !== $idx + 3 || !$this->isNextLine($next - 1));
            if ($newGroup) {
                if (count($group) > 1) {
                    $groups[] = $group;
                }
                $group = [];
            }

            $item = $this->groupData($next);
            $idx  = $item[0];
            $group[] = $item;
        }

        if (count($group) > 1) {
            $groups[] = $group;
        }

        return $groups;
    }

    private function nextSequence(int $idx, array $sequence, int $maxRange): ?int
    {
        if ($idx >= $maxRange) { return null; }
        $seq = $this->tokens->findSequence($sequence, $idx, $maxRange);
        if (!$seq) { return null; }
        return array_keys($seq)[0];
    }

    private function groupData($idx): array
    {
        $idx = $this->tokens->getNextTokenOfKind($idx, [[T_VARIABLE]]);
        return [$idx, strlen($this->tokens[$idx - 2]->getContent())];
    }
}
