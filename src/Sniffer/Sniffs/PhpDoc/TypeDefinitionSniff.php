<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Sniffer\Sniffs\PhpDoc;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use Polymorphine\Dev\Sniffer\Tokens;
use Polymorphine\Dev\Tools\PhpDocTypeLine;


final class TypeDefinitionSniff implements Sniff
{
    private const MSG_INVALID_CALLBACK = <<<'WARNING'
        Callable type definition should contain typed signature
        format: `callable(ArgType, ...): ReturnType` or
                `Closure(ArgType, ...): ReturnType`
        WARNING;

    private const MSG_INVALID_ARRAY = <<<'WARNING'
        Array type definition should contain internal type-hints
        format: `array<keyType, valueType>` or `array<valueType>` or
                `array{keyName: valueType, ...}` or `list<valueType>`
        WARNING;

    private const MSG_MALFORMED_TYPE = <<<'WARNING'
        Malformed type definition - check matching `<>` brackets
        WARNING;

    private Tokens $tokens;

    public function register(): array
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->tokens = new Tokens($phpcsFile->getTokens());
        while ($stackPtr = $this->tokens->findNext($stackPtr, ['T_DOC_COMMENT_TAG'])) {
            $typeTag = in_array($this->tokens->content($stackPtr), ['@param', '@return', '@var'], true);
            if (!$typeTag) { continue; }

            $phpDoc = new PhpDocTypeLine($this->typeDoc($stackPtr + 2));
            $type   = $phpDoc->reducedType();
            if ($type === 'T') { continue; }

            if ($this->containsAny($type, 'callable', 'Closure')) {
                $phpcsFile->addWarning(self::MSG_INVALID_CALLBACK, $stackPtr, 'FoundCallback');
            } elseif ($this->containsAny($type, '[]', 'array', 'list')) {
                $phpcsFile->addWarning(self::MSG_INVALID_ARRAY, $stackPtr, 'FoundArray');
            } else {
                $phpcsFile->addWarning(self::MSG_MALFORMED_TYPE, $stackPtr, 'FoundMalformed');
            }
        }
    }

    private function typeDoc(int $idx): string
    {
        $end     = $this->endingIdx($idx);
        $content = $this->tokens->content($idx);
        if ($end <= $idx) { return $content; }

        while ($idx = $this->tokens->findNext($idx, ['T_DOC_COMMENT_STRING'], $end)) {
            $content .= substr($content, -1) === ',' ? ' ' : '';
            $content .= $this->tokens->content($idx);
        }
        return $content;
    }

    private function endingIdx(int $idx): ?int
    {
        $found = $this->tokens->findNext($idx, ['T_DOC_COMMENT_CLOSE_TAG', 'T_DOC_COMMENT_STAR']);
        if ($found === null) { return $idx; }

        $isEmptyLine = $this->tokens->content($found + 1) === "\n";
        $isNextTag   = !$isEmptyLine && $this->tokens->isType($found + 2, 'T_DOC_COMMENT_TAG');
        return $isEmptyLine || $isNextTag ? $this->tokens->findPrev($found, ["\n"]) : $this->endingIdx($found);
    }

    private function containsAny(string $text, string ...$values): bool
    {
        $isFound = fn (bool $found, string $value) => $found || strpos($text, $value) !== false;
        return array_reduce($values, $isFound, false);
    }
}
