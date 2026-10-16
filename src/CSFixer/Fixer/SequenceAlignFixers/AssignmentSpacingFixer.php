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

use Polymorphine\Dev\CSFixer\Fixer\SequenceAlignFixers;
use PhpCsFixer\Tokenizer\Tokens;


final class AssignmentSpacingFixer extends SequenceAlignFixers
{
    private const TYPES = [T_VARIABLE, T_CONST, T_PUBLIC, T_PROTECTED, T_PRIVATE];

    protected string $name       = 'Polymorphine/assignment_spacing';
    protected string $definition = 'Aligns equal operator for consecutive assignments of similar type.';

    public function getPriority(): int
    {
        return -40;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('=');
    }

    protected function nextSequence(int $idx): ?Sequence
    {
        $assign = $this->tokens->getNextTokenOfKind($idx, ['=']);
        if (!$assign) { return null; }
        if (!$this->isDeclareClause($assign)) {
            $this->tokens->ensureWhitespaceAtIndex($assign + 1, 0, ' ');
            $this->tokens->ensureWhitespaceAtIndex($assign - 1, 1, ' ');
        }

        $lineStart     = $this->isDeclareClause($assign) ? null : $this->prevLineBreak($assign);
        $matchPossible = $lineStart && $this->isPureAssignment($lineStart, $assign);
        $signature     = $matchPossible ? $this->tokenSignature($lineStart, $assign) : [];

        return new Sequence($this->tokens, $assign, $signature);
    }

    protected function alignIndex(int $idx): int
    {
        $start = $this->prevLineBreak($idx);
        return $start ? $this->indentationPointLength($this->prevLineBreak($idx), $idx - 1) : 0;
    }

    private function tokenSignature(int $idx, int $assign): array
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
