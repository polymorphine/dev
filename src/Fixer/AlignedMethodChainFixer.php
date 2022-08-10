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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class AlignedMethodChainFixer implements FixerInterface
{
    use FixerMethods;

    public function getName(): string
    {
        return 'Polymorphine/aligned_method_chain';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Multiline chain method calls should be aligned to first arrow operator.', []);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        if (!$arrows = $tokens->findGivenKind(T_OBJECT_OPERATOR)) {
            return false;
        }

        foreach ($arrows as $idx => $token) {
            if ($tokens[$idx - 1]->isWhitespace() && $tokens[$idx - 2]->getContent() === ')') {
                return true;
            }
        }
        return false;
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
        while ($idx = $this->findNextChain($idx)) {
            $this->alignChain($idx, $this->indentationLength($idx));
        }
    }

    private function findNextChain(int $search): ?int
    {
        $idx = $this->tokens->getNextTokenOfKind($search, [[T_OBJECT_OPERATOR]]);
        if (!$idx) { return null; }

        if ($this->tokens[$idx - 1]->isWhitespace() || !$this->isStartOfMultilineChain($idx)) {
            return $this->findNextChain($idx);
        }

        return $idx;
    }

    private function isStartOfMultilineChain(int $idx): bool
    {
        $next = ($this->tokens[$idx + 2]->getContent() === '(') ? $this->findClosing($idx + 2) + 1 : $idx + 2;
        if ($this->tokens[$next]->isWhitespace() && $this->tokens[$next + 1]->isGivenKind(T_OBJECT_OPERATOR)) {
            return true;
        }

        return $this->tokens[$next]->isGivenKind(T_OBJECT_OPERATOR) ? $this->isStartOfMultilineChain($next) : false;
    }

    private function indentationLength(int $idx): int
    {
        $lineBreak = $this->prevLineBreak($idx);
        $code      = $this->tokens->generatePartialCode($lineBreak, $idx - 1);
        return $this->codeLength($code);
    }

    private function alignChain(int $idx, int $indent): void
    {
        $next = $this->nextIndentation($idx + 2, $indent);
        if (!$next) { return; }

        $replace = $this->tokens[$next]->isWhitespace() && $this->tokens[$next + 1]->isGivenKind(T_OBJECT_OPERATOR);
        $insert  = !$replace && $this->tokens[$next]->isGivenKind(T_OBJECT_OPERATOR);

        if (!$replace && !$insert) { return; }

        if ($replace) {
            $this->tokens[$next] = $this->indentationToken($indent, 1);
        } else {
            $this->tokens->insertAt($next, $this->indentationToken($indent, 1));
        }

        $idx = $this->tokens->getNextTokenOfKind($next, [[T_OBJECT_OPERATOR]]);
        if ($idx) { $this->alignChain($idx, $indent); }
    }

    private function nextIndentation(int $idx, int $indent): ?int
    {
        if ($this->tokens[$idx]->getContent() !== '(') {
            return $this->tokens[$idx]->isWhitespace() ? $idx : null;
        }

        $next    = $this->findClosing($idx) + 1;
        $newLine = $this->nextLineBreak($idx);

        if ($newLine < $next) {
            $this->indentMultilineParam($newLine, $next, $indent + 4);
        }

        return $next;
    }

    private function findClosing(int $idx): ?int
    {
        $parenthesis = $this->tokens->getNextTokenOfKind($idx, ['(', ')']);

        if ($this->tokens[$parenthesis]->getContent() === '(') {
            $nestedEnd = $this->findClosing($parenthesis);
            return $this->findClosing($nestedEnd);
        }

        return $parenthesis;
    }

    private function indentMultilineParam(int $idx, int $end, int $indent): void
    {
        $minLevel = $this->codeLength($this->tokens[$idx]->getContent());

        $diff = $indent - $minLevel;
        if (!$diff) { return; }

        while ($idx < $end) {
            $this->tokens[$idx] = $this->fixIndent($diff, $this->tokens[$idx]);
            $idx = $this->nextLineBreak($idx);
        }
    }

    private function fixIndent(int $length, Token $token): Token
    {
        $code       = $token->getContent();
        $lineBreaks = substr_count($code, "\n");

        $indent = $this->codeLength($code) + $length;
        return $this->indentationToken($indent, $lineBreaks);
    }
}
