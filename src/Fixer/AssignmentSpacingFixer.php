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


final class AssignmentSpacingFixer implements FixerInterface
{
    use FixerMethods;

    private const TYPES = [T_VARIABLE, T_CONST, T_PUBLIC, T_PROTECTED, T_PRIVATE];

    public function getName(): string
    {
        return 'Polymorphine/assignment_spacing';
    }

    public function getDefinition(): FixerDefinitionInterface
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

        $groups  = [];
        $declare = key($this->tokens->findGivenKind(T_DECLARE, 0, 2));
        $assign  = $declare ? $declare + 6 : 1;
        while ($assign = $this->tokens->getNextTokenOfKind($assign, ['='])) {
            $tokens->ensureWhitespaceAtIndex($assign + 1, 0, ' ');
            if (!$siblings = $this->findSiblings($assign)) {
                $tokens->ensureWhitespaceAtIndex($assign - 1, 1, ' ');
                continue;
            }
            $groups[] = $siblings;
            $assign = $this->lastSiblingIdx($siblings);
        }

        foreach ($groups as $siblings) {
            $this->fixGroupIndentation($siblings);
        }
    }

    private function findSiblings(int $assign): array
    {
        $lineStart     = $this->prevLineBreak($assign);
        $matchPossible = $lineStart && $this->isSingleLine($assign) && $this->isPureAssignment($lineStart, $assign);
        if (!$matchPossible) { return []; }

        $siblings  = [];
        $signature = $this->getTokenSignature($lineStart, $assign);

        $idx = $assign;
        while ($sibling = $this->nextSibling($idx, $signature)) {
            $siblings[] = $sibling;
            $idx = $sibling[0];
            if (!$this->isSingleLine($idx)) { break; }
        }

        return $siblings ? [[$assign, $this->indentationPointLength($lineStart, $assign)], ...$siblings] : [];
    }

    private function nextSibling(int $idx, array $signature): ?array
    {
        $newLine = $this->nextLineBreak($idx);
        if (!$newLine || !$this->isNextLine($newLine)) { return null; }

        $assign = $this->tokens->getNextTokenOfKind($newLine, ['=']);
        if (!$assign) { return null; }
        if (!$this->isPureAssignment($newLine, $assign)) { return null; }

        $candidateSignature = $this->getTokenSignature($newLine, $assign);
        if ($candidateSignature !== $signature) { return null; }

        return [$assign, $this->indentationPointLength($newLine, $assign)];
    }

    private function getTokenSignature(int $idx, int $assign): array
    {
        $signature = [];
        while (++$idx <= $assign) {
            $signature[] = $this->tokens[$idx]->getId();
        }
        return $signature;
    }

    private function isSingleLine(int $assign): bool
    {
        $endLine = $this->nextLineBreak($assign);
        return !$endLine || $this->tokens[$endLine - 1]->getContent() === ';';
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

        if ($token->isGivenKind(T_STRING)) {
            return $this->isStaticAssign($newLine);
        }

        return $token->isGivenKind(self::TYPES);
    }

    private function isStaticAssign(int $newLine): bool
    {
        $operator = $this->tokens[$newLine + 2]->isGivenKind(T_PAAMAYIM_NEKUDOTAYIM);
        return $operator && $this->tokens[$newLine + 3]->isGivenKind(T_VARIABLE);
    }
}
