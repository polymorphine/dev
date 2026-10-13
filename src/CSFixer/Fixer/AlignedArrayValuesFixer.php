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
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\CT;
use SplFileInfo;


final class AlignedArrayValuesFixer implements FixerInterface
{
    use FixerMethods;

    public function getName(): string
    {
        return 'Polymorphine/aligned_array_values';
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition('Aligns arrow operator in multiline arrays.', []);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOUBLE_ARROW);
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

        $idx = 0;
        while ($idx = $this->tokens->getNextTokenOfKind($idx, [[CT::T_ARRAY_BRACKET_OPEN]])) {
            $this->alignIndentation($idx);
            $idx = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $idx);
        }
    }

    private function alignIndentation(int $idx): void
    {
        $tokenTypes = [[CT::T_ARRAY_BRACKET_OPEN], [T_DOUBLE_ARROW], [CT::T_ARRAY_BRACKET_CLOSE]];
        $group      = [];
        while ($idx = $this->tokens->getNextTokenOfKind($idx, $tokenTypes)) {
            $type = $this->tokens[$idx]->getId();
            if ($type === CT::T_ARRAY_BRACKET_CLOSE) { break; }
            if ($type === CT::T_ARRAY_BRACKET_OPEN) {
                $this->alignIndentation($idx);
                $idx = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $idx);
                continue;
            }
            $lineEnd = $this->nextLineBreak($idx);
            if ($this->isMultipleAssign($idx, $lineEnd)) { return; }

            $lineStart = $this->prevLineBreak($idx);
            $group[$idx] = $this->indentationPointLength($lineStart, $idx);
        }
        if (count($group) < 2) { return; }
        $this->fixGroupIndentation($group);
    }

    private function isMultipleAssign(int $firstArrow, int $lineEnd): bool
    {
        $last = $this->tokens->getPrevTokenOfKind($lineEnd, [[T_DOUBLE_ARROW], [CT::T_ARRAY_BRACKET_CLOSE]]);
        if ($this->tokens[$last]->isGivenKind(CT::T_ARRAY_BRACKET_CLOSE)) {
            $continueFrom = $this->tokens->findBlockStart(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $last);
            return $continueFrom < $firstArrow || $this->isMultipleAssign($firstArrow, $continueFrom);
        }
        return $firstArrow !== $last;
    }
}
