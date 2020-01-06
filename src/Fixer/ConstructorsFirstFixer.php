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

use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class ConstructorsFirstFixer implements DefinedFixerInterface
{
    private $constructors = [];

    public function getName()
    {
        return 'Polymorphine/constructors_first';
    }

    public function getPriority()
    {
        //assumed one line method spacing
        return -40;
    }

    public function getDefinition()
    {
        return new FixerDefinition(
            'Constructors should be placed before other methods.',
            [
                new CodeSample("<?php\nclass MyClass\n{\n    private \$property;\n\n
    public function doSomething() {\n    }\n\n    public function __construct() {\n    }"),
                new CodeSample("<?php\nclass MyClass\n{\n    public function __construct() {\n    }\n\n
    public function doSomething() {\n    }\n\n    public static function createWithArray() {\n    }\n}")
            ]
        );
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
        return $tokens->isAnyTokenKindsFound([T_CLASS]);
    }

    public function fix(SplFileInfo $file, Tokens $tokens)
    {
        $this->constructors = [];

        $firstMethod = min(array_filter([
            $this->getSequenceStartId([[T_PUBLIC], [T_FUNCTION]], $tokens),
            $this->getSequenceStartId([[T_PUBLIC], [T_ABSTRACT], [T_FUNCTION]], $tokens)
        ]) + [0]);

        if (!$firstMethod) { return; }

        if ($mainConstructor = $this->getConstructorIdx($tokens)) {
            $this->extractMethod($mainConstructor, $tokens);
        }

        $idx = 0;
        while ($staticIdx = $this->getSequenceStartId([[T_PUBLIC], [T_STATIC], [T_FUNCTION]], $tokens, $idx)) {
            $previous = $tokens->getPrevNonWhitespace($staticIdx);
            if ($tokens[$previous]->isComment()) {
                $staticIdx = $previous;
            }
            $this->extractMethod($staticIdx, $tokens);
            $idx = $staticIdx + 5;
        }

        $tokens->insertAt($firstMethod, Tokens::fromArray($this->constructors));
    }

    private function extractMethod($idx, Tokens $tokens)
    {
        $beginBlock = $tokens->getNextTokenOfKind($idx, ['{']);
        $endBlock   = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $beginBlock) + 1;

        if ($this->isLastMethod($endBlock, $tokens)) {
            $previousBreak = $tokens->getPrevMeaningfulToken($idx) + 1;
            $break         = $tokens[$previousBreak];
            $tokens[$previousBreak] = $tokens[$endBlock];
            $tokens[$endBlock]      = $break;
        }

        while ($idx <= $endBlock) {
            $this->constructors[] = $tokens[$idx];
            $tokens->clearAt($idx);
            $idx++;
        }
    }

    private function getConstructorIdx(Tokens $tokens, $idx = 0)
    {
        $start = $this->getSequenceStartId([[T_PUBLIC], [T_FUNCTION], [T_STRING]], $tokens, $idx);

        if (!$start) { return null; }

        if ($tokens[$start + 4]->getContent() !== '__construct') {
            return $this->getConstructorIdx($tokens, $start + 5);
        }

        $previous = $tokens->getPrevNonWhitespace($start);
        if ($tokens[$previous]->isComment()) {
            $start = $previous;
        }

        return $start;
    }

    private function getSequenceStartId(array $sequence, Tokens $tokens, $idx = 0)
    {
        $sequence = $tokens->findSequence($sequence, $idx);

        return ($sequence) ? array_keys($sequence)[0] : null;
    }

    private function isLastMethod($whitespaceIdx, Tokens $tokens): bool
    {
        $next = $tokens->getNextMeaningfulToken($whitespaceIdx);
        return $tokens[$next]->getContent() === '}';
    }
}
