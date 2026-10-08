<?php declare(strict_types=1);

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
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class AlignedTypedPropertiesFixer implements FixerInterface
{
    use FixerMethods;

    public function getName(): string
    {
        return 'Polymorphine/aligned_properties';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Aligns variables in class declarations.', []);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getPriority(): int
    {
        return -39;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_CLASS, T_TRAIT]);
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $this->tokens = $tokens;

        $idx    = $this->tokens->getNextTokenOfKind(0, [[T_CLASS], [T_TRAIT]]);
        $groups = $this->findGroups($this->tokens->getNextTokenOfKind($idx, ['{']));
        foreach ($groups as $group) {
            $this->fixGroupIndentation($group);
        }
    }

    private function findGroups(int $idx): array
    {
        $groups = [];
        $group  = [];
        $prev   = new Sequence($this->tokens, $idx);
        while ($sequence = $this->nextSequence($prev->endIdx)) {
            if (!$prev->sameGroup($sequence)) {
                count($group) > 1 && $groups[] = $group;
                $group = [];
            }

            $group[$sequence->endIdx] = $this->alignIndex($sequence->endIdx);
            $prev = $sequence;
        }
        return count($group) > 1 ? [...$groups, $group] : $groups;
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

    private function alignIndex(int $idx): int
    {
        $start = $this->tokens->getPrevTokenOfKind($idx, [[T_PRIVATE], [T_PROTECTED], [T_PUBLIC]]);
        return $this->indentationPointLength($start + 2, $idx - 1);
    }
}
