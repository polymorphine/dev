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
    private $constructors = [];

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
        $this->constructors = [];

        $firstMethod     = $this->getFirstMethodIdx($tokens);
        $mainConstructor = $this->getMainConstructorIdx($tokens);

        if (!$firstMethod) {
            if (!$mainConstructor) { return; }
            $firstConstructor = $this->getNextConstructorIdx($tokens);
            if ($firstConstructor >= $mainConstructor) { return; }
            $this->extractMethod($mainConstructor, $tokens);
            $tokens->insertAt($firstConstructor, Tokens::fromArray($this->constructors));
            return;
        }

        if ($firstMethod < $mainConstructor) {
            $this->extractMethod($mainConstructor, $tokens);
        }

        $idx = $firstMethod;
        while ($idx = $this->getNextConstructorIdx($tokens, $idx + 10)) {
            $this->extractMethod($idx, $tokens);
        }

        $tokens->insertAt($firstMethod, Tokens::fromArray($this->constructors));
    }

    private function getFirstMethodIdx(Tokens $tokens): int
    {
        $idx = $tokens->getNextTokenOfKind(0, [[T_FUNCTION]]);
        while ($idx && $this->isConstructor($idx, $tokens)) {
            $idx = $tokens->getNextTokenOfKind($idx, [[T_FUNCTION]]);
        }

        return $idx ? $this->methodBeginIdx($idx, $tokens) : 0;
    }

    private function getMainConstructorIdx(Tokens $tokens): int
    {
        $idx = $tokens->getNextTokenOfKind(0, [[T_FUNCTION]]);
        while ($idx && !$this->isMainConstructor($idx, $tokens)) {
            $idx = $tokens->getNextTokenOfKind($idx, [[T_FUNCTION]]);
        }

        return $idx ? $this->methodBeginIdx($idx, $tokens) : 0;
    }

    private function getNextConstructorIdx(Tokens $tokens, int $idx = 0): int
    {
        $idx = $tokens->getNextTokenOfKind($idx, [[T_FUNCTION]]);
        while ($idx && !$this->isConstructor($idx, $tokens)) {
            $idx = $tokens->getNextTokenOfKind($idx, [[T_FUNCTION]]);
        }

        return $idx ? $this->methodBeginIdx($idx, $tokens) : 0;
    }

    private function methodBeginIdx($idx, Tokens $tokens): int
    {
        $definition = [T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FINAL, T_ABSTRACT, T_FUNCTION];
        while ($tokens[$idx]->isGivenKind($definition)) {
            $idx = $tokens->getPrevMeaningfulToken($idx);
        }
        return $idx + 1;
    }

    private function isConstructor(int $idx, Tokens $tokens): bool
    {
        $isStaticConstructor = $tokens[$idx - 2]->isGivenKind(T_STATIC) && $tokens[$idx - 4]->isGivenKind(T_PUBLIC);
        return $isStaticConstructor || $this->isMainConstructor($idx, $tokens);
    }

    private function isMainConstructor(int $idx, Tokens $tokens): bool
    {
        return $tokens[$idx + 2]->getContent() === '__construct';
    }

    private function extractMethod($idx, Tokens $tokens)
    {
        $beginBlock = $tokens->getNextTokenOfKind($idx, ['{']);
        $endBlock   = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $beginBlock);

        while ($idx <= $endBlock) {
            if ($tokens[$idx]->getContent() === '') {
                $idx++;
                continue;
            }
            $this->constructors[] = $tokens[$idx];
            $tokens->clearAt($idx);
            $idx++;
        }
    }
}
