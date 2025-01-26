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
use PhpCsFixer\Tokenizer\CT;
use SplFileInfo;


final class NoTrailingCommaInMultilineArrayFixer implements FixerInterface
{
    public function getName(): string
    {
        return 'Polymorphine/no_trailing_comma_after_multiline_array';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Removes trailing comma from multiline arrays.', []);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getPriority(): int
    {
        return -40;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN]);
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($idx = $tokens->count() - 1; $idx >= 0; --$idx) {
            if (!$this->isMultilineArray($tokens, $idx)) { continue; }
            $this->fixArray($tokens, $idx);
        }
    }

    private function isMultilineArray(Tokens $tokens, int $idx): bool
    {
        $isArray = $tokens[$idx]->isGivenKind([T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN]);
        if (!$isArray) { return false; }

        $openBrace = $tokens[$idx]->isGivenKind(T_ARRAY) ? $tokens->getNextMeaningfulToken($idx) : $idx;
        $blockType = Tokens::detectBlockType($tokens[$openBrace]);
        if (!$blockType || !$blockType['isStart']) { return false; }

        $endIndex = $tokens->findBlockEnd($blockType['type'], $openBrace);
        for ($index = $openBrace + 1; $index < $endIndex; ++$index) {
            $token     = $tokens[$index];
            $blockType = Tokens::detectBlockType($token);

            if ($blockType && $blockType['isStart']) {
                $index = $tokens->findBlockEnd($blockType['type'], $index);
                continue;
            }

            $isLineBreak = $token->isWhitespace() && strpos($token->getContent(), "\n") !== false;
            if ($isLineBreak) { return true; }
        }

        return false;
    }

    private function fixArray(Tokens $tokens, int $idx): void
    {
        $startIdx = $idx;

        if ($tokens[$startIdx]->isGivenKind(T_ARRAY)) {
            $startIdx = $tokens->getNextTokenOfKind($startIdx, ['(']);
            $endIdx   = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $startIdx);
        } else {
            $endIdx = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $startIdx);
        }

        $beforeEndIdx   = $tokens->getPrevMeaningfulToken($endIdx);
        $beforeEndToken = $tokens[$beforeEndIdx];

        if ($beforeEndToken->equals(',')) { $tokens->clearAt($beforeEndIdx); }
    }
}
