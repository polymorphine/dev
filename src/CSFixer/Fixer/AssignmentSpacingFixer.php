<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CSFixer\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use Polymorphine\Dev\CSFixer\Tokens\FixerMethods;
use Polymorphine\Dev\CSFixer\Tokens\Sequence;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class AssignmentSpacingFixer implements FixerInterface
{
    use FixerMethods;

    private const TYPES = [T_VARIABLE, T_CONST, T_PUBLIC, T_PROTECTED, T_PRIVATE];

    public function getName(): string
    {
        return 'Polymorphine/assignment_spacing';
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition('Aligns equal operator for consecutive assignments of similar type.', []);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('=');
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return -40;
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $this->tokens = $tokens;
        foreach ($this->findGroups(0) as $group) {
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
        $assign = $this->tokens->getNextTokenOfKind($idx, ['=']);
        if (!$assign) { return null; }
        if ($this->isDeclareClause($assign)) {
            return $this->nextSequence($assign);
        }
        $this->tokens->ensureWhitespaceAtIndex($assign + 1, 0, ' ');

        $lineStart     = $this->prevLineBreak($assign);
        $matchPossible = $lineStart && $this->isPureAssignment($lineStart, $assign);
        $this->tokens->ensureWhitespaceAtIndex($assign - 1, 1, ' ');
        if (!$matchPossible) { return $this->nextSequence($assign); }

        return new Sequence($this->tokens, $assign, $this->getTokenSignature($lineStart, $assign));
    }

    private function alignIndex(int $idx): int
    {
        return $this->indentationPointLength($this->prevLineBreak($idx), $idx - 1);
    }

    private function getTokenSignature(int $idx, int $assign): array
    {
        $signature = [];
        while (++$idx <= $assign) {
            $signature[] = $this->tokens[$idx]->getId();
        }
        return $signature;
    }

    private function isPureAssignment(int $newLine, int $assign): bool
    {
        if (!$this->validAssignType($newLine)) { return false; }

        $idx = $newLine;
        while ($idx++ < $assign) {
            if ($this->tokens[$idx]->getContent() === '(') { return false; }
        }
        return true;
    }

    private function validAssignType(int $newLine): bool
    {
        $token = $this->tokens[$newLine + 1];
        return $token->isGivenKind(T_STRING) ? $this->isStaticAssign($newLine) : $token->isGivenKind(self::TYPES);
    }

    private function isStaticAssign(int $newLine): bool
    {
        $operator = $this->tokens[$newLine + 2]->isGivenKind(T_PAAMAYIM_NEKUDOTAYIM);
        return $operator && $this->tokens[$newLine + 3]->isGivenKind(T_VARIABLE);
    }

    private function isDeclareClause(int $opIdx): bool
    {
        $prev = $this->meaningfulToken($opIdx, -3);
        return $prev && $this->tokens[$prev]->isGivenKind(T_DECLARE);
    }
}
