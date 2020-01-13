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
        if (!$firstMethod = $this->getFirstMethodIdx($tokens)) { return; }

        $mainConstructor = $this->getFirstMethodIdx($tokens, true);
        if ($firstMethod < $mainConstructor) {
            $this->extractMethod($mainConstructor, $tokens);
        }

        $idx = 0;
        while ($idx = $this->getNextStaticConstructorIdx($tokens, $idx)) {
            if ($idx < $mainConstructor || $idx > $firstMethod) {
                $this->extractMethod($idx, $tokens);
            }
            $idx += 5;
        }

        $tokens->insertAt($firstMethod, Tokens::fromArray($this->constructors));
    }

    private function getFirstMethodIdx(Tokens $tokens, bool $constructor = false): int
    {
        $idx = $tokens->getNextTokenOfKind(0, [[T_FUNCTION]]);
        while ($this->isConstructor($idx, $tokens) !== $constructor) {
            $idx = $tokens->getNextTokenOfKind($idx, [[T_FUNCTION]]);
            if (!$idx) { return 0; }
        }

        return $this->methodBeginIdx($idx, $tokens);
    }

    private function getNextStaticConstructorIdx(Tokens $tokens, $idx)
    {
        $definition = $this->getSequenceStartIdx([[T_PUBLIC], [T_STATIC], [T_FUNCTION]], $tokens, $idx);
        return $definition ? $this->methodBeginIdx($definition, $tokens) : 0;
    }

    private function getSequenceStartIdx(array $sequence, Tokens $tokens, $idx = 0)
    {
        $sequence = $tokens->findSequence($sequence, $idx);
        return ($sequence) ? array_keys($sequence)[0] : null;
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
