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


final class ValidTagsSniff implements Sniff
{
    private const TYPE_REQUIRED = ['callable', 'Closure', 'array', 'iterable', 'Traversable', 'Iterator', 'Generator'];
    private const TYPE_TOKENS   = ['T_STRING', 'T_CALLABLE', 'T_NULLABLE', 'T_NS_SEPARATOR'];

    private const MSG_INVALID_PHPDOC = 'Invalid phpDoc tags for method signature';
    private const MSG_MISSING_PHPDOC = 'Missing required phpDoc for %s type signature';
    private const MSG_UNEXPECTED_VAR = 'Unknown variable definition';
    private const MSG_WRONG_ORDER    = 'Wrong argument order';

    private Tokens $tokens;

    public function register(): array
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->tokens = new Tokens($phpcsFile->getTokens());

        while ($stackPtr = $this->tokens->findNext($stackPtr, ['T_FUNCTION'])) {
            $lineBreak    = $this->tokens->findPrev($stackPtr, ["\n"]);
            $isDocumented = $this->tokens->isType($lineBreak - 1, ['T_DOC_COMMENT_CLOSE_TAG']);
            if (!$isDocumented) { continue; }

            $endIdx = $this->tokens->findNext($stackPtr, ['T_SEMICOLON', 'T_OPEN_CURLY_BRACKET']);
            $types  = $this->typeDeclarations($stackPtr, $endIdx);

            $phpDoc = $this->tokens->findPrev($lineBreak - 1, ['T_DOC_COMMENT_OPEN_TAG']);
            $this->validateTags($phpcsFile, $phpDoc, $lineBreak - 1, $types);
        }
    }

    private function validateTags(File $phpcsFile, int $idx, int $end, array $types): void
    {
        $start = $idx;
        $order = array_flip(array_keys($types));
        $prev  = 0;
        while ($idx = $this->tokens->findNext($idx, ['@param', '@return'], $end)) {
            $phpDoc  = new PhpDocTypeLine($this->tokens->typeDoc($idx));
            $varName = $phpDoc->variableName() ?: '@return';

            if (!isset($types[$varName])) {
                $phpcsFile->addError(self::MSG_UNEXPECTED_VAR, $idx, 'Unexpected');
                continue;
            }

            if ($order[$varName] < $prev) {
                $phpcsFile->addWarning(self::MSG_WRONG_ORDER, $idx, 'ArgumentOrder');
            }

            if ($types[$varName] && $types[$varName] !== $phpDoc->simplifiedType()) {
                $phpcsFile->addError(self::MSG_INVALID_PHPDOC, $idx, 'Invalid');
            }

            unset($types[$varName]);
            $prev = $order[$varName];
        }

        foreach ($types as $varName => $type) {
            $isRequired = $type && in_array(trim($type, '?\\'), self::TYPE_REQUIRED, true);
            if (!$isRequired) { continue; }

            $message = sprintf(self::MSG_MISSING_PHPDOC, $varName === '@return' ? '@return' : '@param ' . $varName);
            $phpcsFile->addError($message, $start, 'Required');
        }
    }

    private function typeDeclarations(int $idx, int $endIdx): array
    {
        $idx = $this->tokens->findNext($idx, ['T_OPEN_PARENTHESIS'], $endIdx);
        $end = $this->tokens->findNext($idx, ['T_CLOSE_PARENTHESIS'], $endIdx);

        $args = [];
        $prev = $idx;
        while ($idx = $this->tokens->findNext($idx, ['T_VARIABLE'], $end)) {
            $name = $this->tokens->content($idx);
            $args[$name] = $this->typeString($prev, $idx);
            $prev = $idx + 1;
        }
        $args['@return'] = $this->typeString($end, $endIdx);
        return $args;
    }

    private function typeString(int $idx, $max): string
    {
        $start = $this->tokens->findNext($idx, self::TYPE_TOKENS, $max);
        if (!$start) { return ''; }

        $idx = $start;
        while ($this->tokens->isType($idx + 1, self::TYPE_TOKENS)) {
            $idx++;
        }
        return $idx === $start ? $this->tokens->content($idx) : $this->tokens->content($start, $idx);
    }
}
