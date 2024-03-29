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


final class AlignedAssignmentsFixer implements FixerInterface
{
    use FixerMethods;

    private const TYPES = [T_VARIABLE, T_CONST, T_PUBLIC, T_PROTECTED, T_PRIVATE];

    public function getName(): string
    {
        return 'Polymorphine/aligned_assignments';
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

        $groups = [];
        $assign = 0;
        while ($assign = $this->tokens->getNextTokenOfKind($assign, ['='])) {
            $newLine = $this->prevLineBreak($assign);
            if (!$this->isPureAssignment($newLine, $assign)) { continue; }

            $siblings = $this->findSiblings($newLine, $assign);
            if (!$siblings) { continue; }

            $groups[] = $siblings;
            $assign = $this->lastSiblingIdx($siblings);
        }

        foreach ($groups as $siblings) {
            $this->fixGroupIndentation($siblings);
        }
    }

    private function findSiblings(int $newLine, int $assign): ?array
    {
        $siblings  = [];
        $signature = $this->getTokenSignature($newLine, $assign);

        $idx = $assign;
        while ($sibling = $this->findNextSibling($idx, $signature)) {
            $siblings[] = $sibling;
            $idx = $sibling[0];
        }

        if (!$siblings) { return null; }

        array_unshift($siblings, [$assign, $this->indentationPointLength($newLine, $assign)]);
        return $siblings;
    }

    private function findNextSibling(int $idx, array $signature): ?array
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

    private function isPureAssignment(int $newLine, int $assign): bool
    {
        $endLine = $this->nextLineBreak($assign);
        if ($this->tokens[$endLine - 1]->getContent() !== ';') {
            return false;
        }

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
