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
use ReflectionClass;
use ReflectionMethod;
use Throwable;


final class ValidTagsSniff implements Sniff
{
    private const TYPE_REQUIRED = ['callable', 'Closure', 'array', 'iterable', 'Traversable', 'Iterator', 'Generator'];
    private const TYPE_TOKENS   = ['T_STRING', 'T_CALLABLE', 'T_NULLABLE', 'T_NS_SEPARATOR', 'T_SELF', 'T_STATIC'];

    private const MSG_MISSING_PHPDOC = 'Missing phpDoc comment for original public method signature';
    private const MSG_UNEXPECTED_VAR = 'Unknown variable definition';
    private const MSG_WRONG_ORDER    = 'Wrong argument order';
    private const MSG_TYPE_MISMATCH  = 'Invalid phpDoc tags for method signature';
    private const MSG_MISSING_TAG    = 'Missing required phpDoc for %s type signature';

    private Tokens $tokens;

    public function register(): array
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->tokens = new Tokens($phpcsFile->getTokens());

        $isOrigin    = $this->tokens->isType($stackPtr, ['T_TRAIT', 'T_INTERFACE']);
        $originalApi = $isOrigin ? [] : $this->definedMethods($this->fullClassName($stackPtr));

        while ($stackPtr = $this->tokens->findNext($stackPtr, ['T_FUNCTION'])) {
            $isInherited  = isset($originalApi[$this->tokens->content($stackPtr + 2)]);
            $lineBreak    = $this->tokens->findPrev($stackPtr, ["\n"]);
            $isPublic     = $this->tokens->findNext($lineBreak, ['T_PUBLIC'], $stackPtr - 2) !== null;
            $isDocumented = $this->tokens->isType($lineBreak - 1, ['T_DOC_COMMENT_CLOSE_TAG']);
            $endIdx       = $this->tokens->findNext($stackPtr, ['T_SEMICOLON', 'T_OPEN_CURLY_BRACKET']);

            if (!$isDocumented) {
                if (!$isPublic || $isInherited) { continue; }
                $this->tokens->findNext($stackPtr, self::TYPE_REQUIRED, $endIdx) !== null
                    ? $phpcsFile->addError(self::MSG_MISSING_PHPDOC, $stackPtr, 'RequiredAPI')
                    : $phpcsFile->addWarning(self::MSG_MISSING_PHPDOC, $stackPtr, 'MissingAPI');
                continue;
            }

            $types  = $this->typeDeclarations($stackPtr, $endIdx);
            $phpDoc = $this->tokens->findPrev($lineBreak - 1, ['T_DOC_COMMENT_OPEN_TAG']);
            $this->validateTags($phpcsFile, $phpDoc, $lineBreak - 1, $types, $isInherited);
        }
    }

    private function validateTags(File $phpcsFile, int $idx, int $end, array $types, bool $optional): void
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
                $phpcsFile->addWarning(self::MSG_WRONG_ORDER, $idx, 'Order');
            }

            if ($types[$varName] && $types[$varName] !== $phpDoc->simplifiedType()) {
                $phpcsFile->addError(self::MSG_TYPE_MISMATCH, $idx, 'Invalid');
            }

            unset($types[$varName]);
            $prev = $order[$varName];
        }

        if ($optional) { return; }
        foreach ($types as $varName => $type) {
            $isRequired = $type && in_array(trim($type, '?\\'), self::TYPE_REQUIRED, true);
            if (!$isRequired) { continue; }
            $message = sprintf(self::MSG_MISSING_TAG, $varName === '@return' ? '@return' : '@param ' . $varName);
            $phpcsFile->addError($message, $start, 'TypeRequired');
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

    private function fullClassName(int $idx): string
    {
        $className = $this->tokens->content($idx + 2);
        $nsBegin   = $this->tokens->findNext(0, ['T_NAMESPACE'], $idx);
        if (!$nsBegin) { return $className; }

        $nsEnd     = $this->tokens->findNext($nsBegin, ['T_SEMICOLON']);
        $namespace = $this->tokens->content($nsBegin + 2, $nsEnd - 1);
        return $namespace . '\\' . $className;
    }

    private function definedMethods(string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
            $interfaces = ['parent' => $reflection->getParentClass() ?: null] + $reflection->getInterfaces();
            return array_reduce($interfaces, [$this, 'publicMethodNames'], []);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function publicMethodNames(array $list, ?ReflectionClass $class): array
    {
        if (!$class) { return $list; }
        $methods = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isFinal()) { continue; }
            $methods[$method->getName()] = true;
        }
        return $list + $methods;
    }
}
