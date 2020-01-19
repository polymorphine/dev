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
    private array  $classTypes;

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

        $classIdx = $tokens->getNextTokenOfKind(0, [[T_CLASS]]) + 2;
        $this->classTypes = $this->getConstructorTypes($classIdx);

        $topMethod     = $this->getMethodIdx($classIdx);
        $isConstructor = function (int $idx) { return $this->tokens[$idx + 2]->getContent() === '__construct'; };
        $construct     = $this->getMethodIdx($topMethod, $isConstructor);
        if ($construct && $construct !== $topMethod) {
            $this->extractMethod($construct);
            $this->moveConstructorsTo($topMethod);

            $topMethod += count($this->constructors);
            $this->constructors = [];
        }

        $notConstructor = function (int $idx) { return !$this->isStaticConstructor($idx); };
        $insertIdx      = $this->getMethodIdx($topMethod, $notConstructor);
        if (!$insertIdx) { return; }

        $isConstructor = function (int $idx) { return $this->isStaticConstructor($idx); };
        $idx           = $insertIdx;
        while ($idx = $this->getMethodIdx($idx + 10, $isConstructor)) {
            $this->extractMethod($idx);
        }

        $this->moveConstructorsTo($insertIdx);
    }

    private function isStaticConstructor(int $idx): bool
    {
        $static = $this->tokens[$idx - 2]->isGivenKind(T_STATIC) && $this->tokens[$idx - 4]->isGivenKind(T_PUBLIC);
        if (!$static) { return false; }

        $openBrace  = $this->tokens->getNextTokenOfKind($idx + 4, ['{']);
        $returnType = $this->tokens[$this->tokens->getPrevMeaningfulToken($openBrace)];

        return $returnType->isGivenKind(T_STRING) && isset($this->classTypes[$returnType->getContent()]);
    }

    private function getMethodIdx(int $start, callable $condition = null): int
    {
        $idx = $this->tokens->getNextTokenOfKind($start, [[T_FUNCTION]]);
        while ($idx && $condition && !$condition($idx)) {
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

    private function getConstructorTypes(int $class): array
    {
        $classTypes = ['self', $this->tokens[$class]->getContent()];

        if ($this->tokens[$class + 2]->isGivenKind(T_EXTENDS)) {
            $class = $class + 4;
            $classTypes[] = $this->tokens[$class]->getContent();
        }

        if ($this->tokens[$class + 2]->isGivenKind(T_IMPLEMENTS)) {
            $classTypes[] = $this->tokens[$class + 4]->getContent();
        }

        return array_flip($classTypes);
    }

    private function moveConstructorsTo(int $insertIdx): void
    {
        if (!$this->constructors) { return; }

        $topIndent = $this->tokens[$insertIdx];
        $this->tokens[$insertIdx] = $this->constructors[0];
        $this->constructors[0] = $topIndent;

        $this->tokens->insertAt($insertIdx, Tokens::fromArray($this->constructors));
    }
}
