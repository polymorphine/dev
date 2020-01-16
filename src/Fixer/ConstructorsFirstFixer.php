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


final class ConstructorsFirstFixer implements FixerInterface
{
    private Tokens $tokens;
    private array  $constructors;

    public function getName()
    {
        return 'Polymorphine/constructors_first';
    }

    public function getPriority()
    {
        return -40;
    }

    public function isRisky()
    {
        return false;
    }

    public function supports(SplFileInfo $file)
    {
        return true;
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([T_CLASS, T_FUNCTION]);
    }

    public function fix(SplFileInfo $file, Tokens $tokens)
    {
        $this->tokens       = $tokens;
        $this->constructors = [];

        $firstMethod      = $this->getMethodIdx(0, function (int $idx) { return !$this->isConstructor($idx); });
        $mainConstructor  = $this->getMethodIdx(0, function (int $idx) { return $this->isMainConstructor($idx); });
        $constructorCheck = function (int $idx) { return $this->isConstructor($idx); };

        if (!$firstMethod) {
            if (!$mainConstructor) { return; }
            $firstConstructor = $this->getMethodIdx(0, $constructorCheck);
            if ($firstConstructor === $mainConstructor) { return; }
            $this->extractMethod($mainConstructor);
            $this->tokens->insertAt($firstConstructor, Tokens::fromArray($this->constructors));
            return;
        }

        if ($mainConstructor > $firstMethod) {
            $this->extractMethod($mainConstructor);
        }

        $idx = $firstMethod;
        while ($idx = $this->getMethodIdx($idx + 10, $constructorCheck)) {
            $this->extractMethod($idx);
        }

        $this->tokens->insertAt($firstMethod, Tokens::fromArray($this->constructors));
    }

    private function isConstructor(int $idx): bool
    {
        if ($this->isMainConstructor($idx)) { return true; }

        $static = $this->tokens[$idx - 2]->isGivenKind(T_STATIC) && $this->tokens[$idx - 4]->isGivenKind(T_PUBLIC);
        if (!$static) { return false; }

        $openBrace  = $this->tokens->getNextTokenOfKind($idx + 4, ['{']);
        $returnType = $this->tokens[$this->tokens->getPrevMeaningfulToken($openBrace)];

        return $returnType->isGivenKind(T_STRING) && $returnType->getContent() === 'self';
    }

    private function isMainConstructor(int $idx): bool
    {
        return $this->tokens[$idx + 2]->getContent() === '__construct';
    }

    private function getMethodIdx(int $start, callable $condition): int
    {
        $idx = $this->tokens->getNextTokenOfKind($start, [[T_FUNCTION]]);
        while ($idx && !$condition($idx)) {
            $idx = $this->tokens->getNextTokenOfKind($idx, [[T_FUNCTION]]);
        }

        if (!$idx) { return 0; }

        $definition = [T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FINAL, T_ABSTRACT, T_FUNCTION];
        while ($this->tokens[$idx]->isGivenKind($definition)) {
            $idx = $this->tokens->getPrevMeaningfulToken($idx);
        }
        return $idx + 1;
    }

    private function extractMethod($idx)
    {
        $beginBlock = $this->tokens->getNextTokenOfKind($idx, ['{']);
        $endBlock   = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $beginBlock);

        while ($idx <= $endBlock) {
            if ($this->tokens[$idx]->getContent() === '') {
                $idx++;
                continue;
            }
            $this->constructors[] = $this->tokens[$idx];
            $this->tokens->clearAt($idx);
            $idx++;
        }
    }
}
