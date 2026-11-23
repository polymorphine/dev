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
use Polymorphine\Dev\CSFixer\AlignTokens;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class AlignedMethodChainFixer implements FixerInterface
{
    private AlignTokens $tokens;

    public function getName(): string
    {
        return 'Polymorphine/aligned_method_chain';
    }

    public function getDefinition(): FixerDefinition
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
        $this->tokens = new AlignTokens($tokens);
        while ($idx = $this->findNextChain($idx ?? 0)) {
            $this->alignChain($idx, $this->tokens->alignColumn($idx, true));
        }
    }

    private function findNextChain(int $opIdx): ?int
    {
        $idx = $this->tokens->nextTokenOfKind($opIdx, [T_OBJECT_OPERATOR]);
        if (!$idx) { return null; }

        $methodChainPossible = !$this->tokens[$idx - 1]->isWhitespace() && $this->isStartOfMultilineChain($idx);
        return $methodChainPossible ? $idx : $this->findNextChain($idx);
    }

    private function isStartOfMultilineChain(int $opIdx): bool
    {
        $idx = $this->tokens[$opIdx + 2]->getContent() === '(' ? $this->findClosing($opIdx + 2) + 1 : $opIdx + 2;
        return $this->tokens[$idx]->isWhitespace() && $this->tokens[$idx + 1]->isGivenKind(T_OBJECT_OPERATOR)
            || $this->tokens[$idx]->isGivenKind(T_OBJECT_OPERATOR) && $this->isStartOfMultilineChain($idx);
    }

    private function alignChain(int $idx, int $indent): void
    {
        $next = $this->nextIndentation($idx + 2, $indent);
        if (!$next) { return; }

        $replace = $this->tokens[$next]->isWhitespace() && $this->tokens[$next + 1]->isGivenKind(T_OBJECT_OPERATOR);
        $insert  = !$replace && $this->tokens[$next]->isGivenKind(T_OBJECT_OPERATOR);
        if (!$replace && !$insert) { return; }

        $this->tokens->whitespaceAfter($next - 1, "\n" . str_repeat(' ', $indent));

        $idx = $this->tokens->nextTokenOfKind($next, [T_OBJECT_OPERATOR]);
        if ($idx) { $this->alignChain($idx, $indent); }
    }

    private function nextIndentation(int $idx, int $indent): ?int
    {
        if ($this->tokens[$idx]->getContent() !== '(') {
            return $this->tokens[$idx]->isWhitespace() ? $idx : null;
        }

        $next    = $this->findClosing($idx) + 1;
        $newLine = $this->tokens->nextLineBreak($idx);

        if ($newLine < $next) {
            $this->indentMultilineParam($newLine, $next, $indent + 4);
        }

        return $next;
    }

    private function findClosing(int $idx): ?int
    {
        $parenthesis = $this->tokens->nextTokenOfKind($idx, ['(', ')']);
        return $this->tokens[$parenthesis]->getContent() === '('
            ? $this->findClosing($this->findClosing($parenthesis))
            : $parenthesis;
    }

    private function indentMultilineParam(int $idx, int $end, int $indent): void
    {
        $minLevel = mb_strlen(ltrim($this->tokens[$idx]->getContent(), "\n"));
        $diff     = $indent - $minLevel;
        if (!$diff) { return; }

        while ($idx < $end) {
            $whitespace = $this->tokens[$idx]->getContent() . str_repeat(' ', $diff);
            $this->tokens->whitespaceAfter($idx - 1, $whitespace);
            $idx = $this->tokens->nextLineBreak($idx);
        }
    }
}
