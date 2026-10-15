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

use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\WhitespacesFixerConfig;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Preg;
use SplFileInfo;


/**
 * Refactored & customized original fixer class.
 *
 * @see \PhpCsFixer\Fixer\Basic\BracesPositionFixer
 */
final class BracesPositionFixer implements WhitespacesAwareFixerInterface
{
    private const CONTROL_STR_TOKENS = [
        T_IF, T_ELSE, T_ELSEIF, T_SWITCH, T_FOR, T_FOREACH,
        T_DO, T_WHILE, T_TRY, T_CATCH, T_FINALLY, T_DECLARE
    ];

    private const CONTROL_CONT_TOKENS = [T_CATCH, T_ELSE, T_ELSEIF, T_FINALLY, T_WHILE];

    private const EXPECTED_TOKENS = [
        T_STRING, T_NS_SEPARATOR, T_STATIC, T_CALLABLE,
        CT::T_TYPE_COLON, CT::T_NULLABLE_TYPE, CT::T_ARRAY_TYPEHINT
    ];

    private string $newLine;

    public function getName(): string
    {
        return 'Polymorphine/braces_position';
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition('Braces must be placed as configured', []);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('{');
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
        return -2;
    }

    public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
    {
        $this->newLine = $config->getLineEnding();
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $classyTokens   = Token::getClassyTokenKinds();
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        foreach ($tokens as $idx => $token) {
            $singleLine   = false;
            $emptyOneLine = false;
            $sameLine     = true;

            if ($token->isGivenKind($classyTokens)) {
                $openBraceIdx = $tokens->getNextTokenOfKind($idx, ['{']);
                $emptyOneLine = $tokensAnalyzer->isAnonymousClass($idx);
                $sameLine     = $emptyOneLine;
            } elseif ($token->isGivenKind(T_FUNCTION)) {
                $openBraceIdx = $tokens->getNextTokenOfKind($idx, ['{', ';']);
                if (!$tokens[$openBraceIdx]->equals('{')) { continue; }
                $singleLine = $tokensAnalyzer->isLambda($idx);
                $sameLine   = $singleLine;
            } elseif ($token->isGivenKind(self::CONTROL_STR_TOKENS)) {
                $this->fixContinuation($idx, $tokens, $tokensAnalyzer);
                $openBraceIdx = $tokens->getNextMeaningfulToken($this->findParenthesisEnd($idx, $tokens));
                if (!$tokens[$openBraceIdx]->equals('{')) { continue; }
                $singleLine = $this->shortConditional($idx, $openBraceIdx, $tokens);
            } else {
                continue;
            }

            $closeBraceIdx = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $openBraceIdx);

            $openBraceEndLine = true;
            if ($singleLine || $emptyOneLine) {
                $openBraceEndLine = false;
                for ($idxInBraces = $openBraceIdx + 1; $idxInBraces < $closeBraceIdx; ++$idxInBraces) {
                    $tokenInBraces    = $tokens[$idxInBraces];
                    $contentToken     = !$tokenInBraces->isWhitespace() && !$tokenInBraces->isComment();
                    $allowedToken     = $contentToken && $emptyOneLine;
                    $openBraceEndLine = $this->hasNewLine($tokenInBraces) || $allowedToken;
                    if ($openBraceEndLine) { break; }
                }
            }

            $openBraceEndLine && $this->addedLinebreakAfterOpenBrace($openBraceIdx, $tokens) && $closeBraceIdx++;

            $sameLine       = $sameLine || $this->dropNewLine($openBraceIdx, $tokens);
            $whitespace     = $sameLine ? ' ' : $this->lineBreakWhitespace($idx, $tokens);
            $moveBraceToIdx = $this->moveBraceToIndex($openBraceIdx, $tokens, $sameLine ? null : $closeBraceIdx);
            $openBraceIdx   = $this->moveToken($openBraceIdx, $moveBraceToIdx, $tokens);
            $tokens->ensureWhitespaceAtIndex($openBraceIdx - 1, 1, $whitespace) && $closeBraceIdx++;

            $openBraceEndLine && $prevIdx = $tokens->getPrevMeaningfulToken($closeBraceIdx);
            if (!$openBraceEndLine || $tokens[$prevIdx]->isGivenKind(T_OPEN_TAG)) {
                continue;
            }

            $prevIdx = $closeBraceIdx - 1;
            while ($tokens->isEmptyAt($prevIdx)) {
                --$prevIdx;
            }

            if ($this->hasNewLine($tokens[$prevIdx])) { continue; }

            $whitespace = $this->lineBreakWhitespace($openBraceIdx, $tokens);
            $tokens->ensureWhitespaceAtIndex($prevIdx, 1, $whitespace);
        }
    }

    private function lineBreakWhitespace(int $idx, Tokens $tokens): string
    {
        return $this->newLine . $this->getLineIndentation($idx, $tokens);
    }

    private function dropNewLine(int $openBraceIndex, Tokens $tokens): bool
    {
        $prevIdx = $tokens->getPrevMeaningfulToken($openBraceIndex);
        while ($tokens[$prevIdx]->isGivenKind(self::EXPECTED_TOKENS)) {
            $prevIdx = $tokens->getPrevMeaningfulToken($prevIdx);
        }
        if (!$tokens[$prevIdx]->equals(')')) { return false; }
        if ($tokens[--$prevIdx]->isComment()) { --$prevIdx; }
        return $this->hasNewLine($tokens[$prevIdx]);
    }

    private function findParenthesisEnd(int $keywordIdx, Tokens $tokens): int
    {
        $nextIndex = $tokens->getNextMeaningfulToken($keywordIdx);
        return $tokens[$nextIndex]->equals('(')
            ? $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $nextIndex)
            : $keywordIdx;
    }

    private function addedLinebreakAfterOpenBrace(int $openBraceIdx, Tokens $tokens): bool
    {
        $changeNotRequired = $this->isFollowedByNewLine($openBraceIdx, $tokens)
            || $this->hasCommentOnSameLine($openBraceIdx, $tokens)
            || $tokens[$tokens->getNextMeaningfulToken($openBraceIdx)]->isGivenKind(T_CLOSE_TAG);
        if ($changeNotRequired) { return false; }

        $whitespace = $this->lineBreakWhitespace($openBraceIdx, $tokens);
        return $tokens->ensureWhitespaceAtIndex($openBraceIdx + 1, 0, $whitespace);
    }

    private function isFollowedByNewLine(int $idx, Tokens $tokens): bool
    {
        for (++$idx, $max = count($tokens) - 1; $idx < $max; ++$idx) {
            if ($tokens[$idx]->isComment()) { continue; }
            return $this->hasNewLine($tokens[$idx]);
        }
        return false;
    }

    private function hasCommentOnSameLine(int $index, Tokens $tokens): bool
    {
        $token = $this->isSingleLine($tokens[$index + 1]) ? $tokens[$index + 2] : $tokens[$index + 1];
        return $token->isComment();
    }

    private function getLineIndentation(int $idx, Tokens $tokens): string
    {
        $newlineIdx = $this->getPreviousNewlineTokenIndex($idx, $tokens);
        return $newlineIdx !== null ? $this->extractIndent($this->computeNewLineContent($newlineIdx, $tokens)) : '';
    }

    private function getPreviousNewlineTokenIndex(int $idx, Tokens $tokens): ?int
    {
        while ($idx = $tokens->getPrevTokenOfKind($idx, [[T_WHITESPACE], [T_INLINE_HTML]])) {
            if ($this->isNewLineToken($idx, $tokens)) { return $idx; }
        }
        return null;
    }

    private function extractIndent(string $content): string
    {
        return Preg::match('/\R(\h*)[^\r\n]*$/D', $content, $matches) ? $matches[1] : '';
    }

    private function computeNewLineContent(int $idx, Tokens $tokens): string
    {
        return $idx && $tokens[$idx - 1]->isGivenKind([T_OPEN_TAG, T_CLOSE_TAG])
            ? Preg::replace('/\S/', '', $tokens[$idx - 1]->getContent()) . $tokens[$idx]->getContent()
            : $tokens[$idx]->getContent();
    }

    private function isNewLineToken(int $idx, Tokens $tokens): bool
    {
        $token = $tokens[$idx];

        $openTagLine = $token->isGivenKind(T_OPEN_TAG)
            && isset($tokens[$idx + 1])
            && !$tokens[$idx + 1]->isWhitespace()
            && Preg::match('/\R/', $token->getContent());
        if ($openTagLine) { return true; }

        $multilineTokens = $token->isGivenKind([T_WHITESPACE, T_INLINE_HTML]);
        if (!$multilineTokens) { return false; }

        return Preg::match('/\R/', $this->computeNewLineContent($idx, $tokens));
    }

    private function hasNewLine(Token $token): bool
    {
        return $token->isWhitespace() && Preg::match('/\R/', $token->getContent());
    }

    private function isSingleLine(Token $token): bool
    {
        return $token->isWhitespace() && !Preg::match('/\R/', $token->getContent());
    }

    private function moveToken(int $fromIdx, int $toIdx, Tokens $tokens): int
    {
        if (!$delta = $toIdx <=> $fromIdx) { return $fromIdx; }

        $this->clearWhitespace($fromIdx, $tokens, $delta < 0);
        $token = $tokens[$fromIdx];
        for ($idx = $fromIdx; $idx !== $toIdx; $idx += $delta) {
            $tokens[$idx] = $tokens[$idx + $delta];
        }
        $tokens[$toIdx] = $token;
        $this->mergeWhitespace($fromIdx, $tokens);
        return $toIdx;
    }

    private function clearWhitespace(int $idx, Tokens $tokens, bool $moveBack): void
    {
        $prev = $idx - 1;
        if (!$tokens[$prev]->isWhitespace()) { return; }
        $moveBack || $tokens->clearAt($prev);
        if (!$moveBack || !$this->hasNewLine($tokens[$prev])) { return; }

        $next    = $idx + 1;
        $content = Preg::replace('/^(\h*?\R)?\h*/', '', $tokens[$next]->getContent());
        $content ? $tokens[$next] = new Token([T_WHITESPACE, $content]) : $tokens->clearAt($next);
    }

    private function mergeWhitespace(int $idx, Tokens $tokens): void
    {
        $bothWhitespace = $tokens[$idx]->isWhitespace() && $tokens[$idx + 1]->isWhitespace();
        if (!$bothWhitespace) { return; }
        $prevWhitespace = $this->hasNewLine($tokens[$idx]) ? $tokens[$idx]->getContent() : '';
        $tokens[$idx] = new Token([T_WHITESPACE, $prevWhitespace . $tokens[$idx + 1]->getContent()]);
        $tokens->clearAt($idx + 1);
    }

    private function moveBraceToIndex(int $openIdx, Tokens $tokens, ?int $closeIdx): int
    {
        return $closeIdx
            ? $this->calculateMultilinePosition($openIdx, $closeIdx, $tokens)
            : $this->calculateSingleLinePosition($openIdx, $tokens);
    }

    private function calculateMultilinePosition(int $openIdx, int $closeIdx, Tokens $tokens): int
    {
        if ($this->hasNewLine($tokens[$openIdx - 1])) { return $openIdx; }
        $move = $openIdx;
        for ($idx = $openIdx + 1; $idx < $closeIdx; ++$idx) {
            if ($this->hasNewLine($tokens[$idx])) { break; }
            $isOneLineComment = $tokens[$idx]->isComment() && substr($tokens[$idx]->getContent(), 0, 2) !== '/*';
            $isOneLineComment && $move = $idx + 1;
        }
        return $move;
    }

    private function calculateSingleLinePosition(int $openIdx, Tokens $tokens): int
    {
        $move    = $openIdx;
        $prevIdx = $tokens->getPrevMeaningfulToken($openIdx);
        for ($idx = $openIdx - 1; $idx > $prevIdx; --$idx) {
            if (!$tokens[$idx]->isComment()) { continue; }
            if ($tokens[--$idx]->isWhitespace()) {
                $move = $idx;
            } elseif ($idx === $prevIdx) {
                $move = $prevIdx + 1;
            }
        }
        return $move;
    }

    private function fixContinuation(int $idx, Tokens $tokens, TokensAnalyzer $analyzer): void
    {
        if (!$tokens[$idx]->isGivenKind(self::CONTROL_CONT_TOKENS)) { return; }

        $prevIdx = $tokens->getPrevNonWhitespace($idx);
        if (!$tokens[$prevIdx]->equals('}')) { return; }

        $isWhile = $tokens[$idx]->isGivenKind(T_WHILE);
        if ($isWhile && !$analyzer->isWhilePartOfDoWhile($idx)) { return; }

        $tokens->ensureWhitespaceAtIndex($idx - 1, 1, ' ');
    }

    private function shortConditional(int $keywordIdx, int $openBraceIdx, Tokens $tokens): bool
    {
        $isCondition = $tokens[$keywordIdx]->isGivenKind(T_IF)
            && $tokens[$keywordIdx - 1]->isWhitespace()
            && !$tokens[$keywordIdx - 2]->isGivenKind(T_ELSE);
        if (!$isCondition) { return false; }

        $closeBraceIdx = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $openBraceIdx);
        $afterCloseIdx = $tokens->getNextMeaningfulToken($closeBraceIdx);
        $isSingleBlock = $afterCloseIdx && $tokens[$afterCloseIdx]->isGivenKind([T_ELSE, T_ELSEIF]);
        if ($isSingleBlock) { return false; }

        $isShortCondition = $this->isShortConditional($keywordIdx, $openBraceIdx, $closeBraceIdx, $tokens);
        if (!$isShortCondition) { return false; }

        $tokens->ensureWhitespaceAtIndex($openBraceIdx + 1, 1, ' ');
        $tokens->ensureWhitespaceAtIndex($closeBraceIdx - 1, 1, ' ');
        return true;
    }

    private function isShortConditional(int $keywordIdx, int $openBraceIdx, int $closeBraceIdx, Tokens $tokens): bool
    {
        $line          = $this->getLineIndentation($keywordIdx, $tokens);
        $commandsLimit = 2;
        $spacesLimit   = 4;
        $isCondition   = false;
        for ($idx = $keywordIdx; $idx <= $closeBraceIdx; $idx++) {
            $token = $tokens[$idx];
            if (!$this->isShortContent($token, $isCondition)) { return false; }
            $token->equalsAny(['(', ')']) && $isCondition = !$isCondition;
            $line .= ($token->isWhitespace() ? ' ' : $token->getContent());
            if ($idx <= $openBraceIdx) { continue; }
            $token->equals(';') && $commandsLimit--;
            $token->isWhitespace() && $spacesLimit--;
            if (!$commandsLimit || !$spacesLimit) { return false; }
        }
        return strlen($line) < 80;
    }

    private function isShortContent(Token $token, bool $isCondition): bool
    {
        if ($token->isComment()) { return false; }
        if ($isCondition && $this->hasNewLine($token)) { return false; }
        $isString = $token->isGivenKind([T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE]);
        if (!$isString) { return true; }
        $string  = $token->getContent();
        $tooLong = strlen($string) > 20 || strpos($string, $this->newLine) !== false;
        return !$tooLong;
    }
}
