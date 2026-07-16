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


final class NamedConstructorsFirstStaticFixer implements FixerInterface
{
    private Tokens $tokens;
    private array  $classTypes;
    private int    $classEnd;

    public function getName(): string
    {
        return 'Polymorphine/named_constructors_first_static';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Moves named static constructor methods to the top.', []);
    }

    public function getPriority(): int
    {
        return -40;
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([T_CLASS, T_FUNCTION]);
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $classIdx = $tokens->getNextTokenOfKind($this->classEnd ?? 0, [[T_CLASS]]) + 2;

        $this->tokens     = $tokens;
        $this->classTypes = $this->classInstanceTypes($classIdx);
        $this->classEnd   = $this->classBodyEnd($classIdx);

        $this->moveStaticConstructors($classIdx);

        if ($tokens->getNextTokenOfKind($this->classEnd, [[T_CLASS]])) {
            $this->fix($file, $tokens);
        }

        unset($this->classEnd, $this->classTypes);
    }

    private function moveStaticConstructors(int $startIdx): void
    {
        $staticIdx = $this->getMethodIdx($startIdx, fn (int $idx) => $this->tokens[$idx - 2]->isGivenKind(T_STATIC));
        if (!$staticIdx) { return; }

        $insertIdx = $this->getMethodIdx($staticIdx, fn (int $idx) => !$this->isStaticConstructor($idx));
        if (!$insertIdx) { return; }

        $idx = $insertIdx;
        while ($idx = $this->getMethodIdx($idx + 10, fn (int $idx) => $this->isStaticConstructor($idx))) {
            $insertIdx = $this->moveMethod($idx, $insertIdx);
        }
    }

    private function isStaticConstructor(int $idx): bool
    {
        $static = $this->tokens[$idx - 2]->isGivenKind(T_STATIC) && $this->tokens[$idx - 4]->isGivenKind(T_PUBLIC);
        if (!$static) { return false; }

        $openBrace  = $this->tokens->getNextTokenOfKind($idx + 4, ['{']);
        $returnType = $this->tokens[$this->tokens->getPrevMeaningfulToken($openBrace)];

        return $returnType->isGivenKind(T_STRING) && isset($this->classTypes[$returnType->getContent()]);
    }

    private function getMethodIdx(int $start, callable $condition): int
    {
        $idx = $this->tokens->getNextTokenOfKind($start, [[T_FUNCTION]]);
        while ($idx && (!$this->isMethod($idx) || !$condition($idx))) {
            $idx = $this->tokens->getNextTokenOfKind($idx, [[T_FUNCTION]]);
        }

        if (!$idx) { return 0; }

        $definition = [T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FINAL, T_ABSTRACT, T_FUNCTION];
        while ($this->tokens[$idx]->isGivenKind($definition)) {
            $idx = $this->tokens->getPrevMeaningfulToken($idx);
        }
        return $this->tokens->getNonEmptySibling($idx, 1);
    }

    private function isMethod(int $idx): bool
    {
        if ($idx > $this->classEnd) { return false; }
        $idx = $this->tokens->getNextMeaningfulToken($idx);
        return $this->tokens[$idx]->isGivenKind(T_STRING);
    }

    private function moveMethod(int $methodIdx, int $insertIdx): int
    {
        $methodTokens = $this->extractMethod($methodIdx);

        $topIndent = $this->tokens[$insertIdx];
        $this->tokens[$insertIdx] = $methodTokens[0];
        $methodTokens[0] = $topIndent;

        $this->tokens->insertAt($insertIdx, Tokens::fromArray($methodTokens));
        $this->classEnd += count($methodTokens);

        return $insertIdx + count($methodTokens);
    }

    private function extractMethod(int $idx): array
    {
        $beginBlock = $this->tokens->getNextTokenOfKind($idx, ['{']);
        $endBlock   = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $beginBlock);

        $methodTokens = [];
        while ($idx <= $endBlock) {
            $methodTokens[] = $this->tokens[$idx];
            $this->tokens->clearAt($idx);
            $idx++;
        }

        return $methodTokens;
    }

    private function classInstanceTypes(int $classIdx): array
    {
        $classTypes = ['self', 'static', $this->tokens[$classIdx]->getContent()];

        if ($this->tokens[$classIdx + 2]->isGivenKind(T_EXTENDS)) {
            $classIdx = $classIdx + 4;
            $classTypes[] = $this->tokens[$classIdx]->getContent();
        }

        if ($this->tokens[$classIdx + 2]->isGivenKind(T_IMPLEMENTS)) {
            $classTypes[] = $this->tokens[$classIdx + 4]->getContent();
        }

        return array_flip($classTypes);
    }

    private function classBodyEnd(int $classIdx): int
    {
        $classBody = $this->tokens->getNextTokenOfKind($classIdx, ['{']);
        return $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classBody);
    }
}
