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
use ReflectionClass;
use ReflectionMethod;
use Throwable;


final class PublicApiOriginSniff implements Sniff
{
    private const MSG_MISSING_PHPDOC = 'Missing phpDoc comment for original public method signature';

    private Tokens $tokens;

    public function register(): array
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->tokens = new Tokens($phpcsFile->getTokens());

        $isInterface = $this->tokens->isType($stackPtr, 'T_INTERFACE');
        $isOrigin    = $isInterface || $this->tokens->isType($stackPtr, 'T_TRAIT');
        $className   = $isOrigin ? null : $this->className($stackPtr);

        $undocumented = [];
        while ($stackPtr = $this->tokens->findNext($stackPtr, ['T_FUNCTION'])) {
            $isApi = $isInterface || $this->tokens->isType($stackPtr - 2, 'T_PUBLIC');
            if (!$isApi) { continue; }

            $lineBreak    = $this->tokens->findPrev($stackPtr, ["\n"]);
            $isDocumented = $this->tokens->isType($lineBreak - 1, 'T_DOC_COMMENT_CLOSE_TAG');
            if ($isDocumented) { continue; }

            $undocumented[$stackPtr] = $this->tokens->content($stackPtr + 2);
        }

        if (!$undocumented) { return; }
        $ancestorMethods = $className ? $this->getAncestorMethods($className) : [];

        foreach ($undocumented as $stackPtr => $methodName) {
            if (isset($ancestorMethods[$methodName])) { continue; }
            $phpcsFile->addWarning(self::MSG_MISSING_PHPDOC, $stackPtr, 'Missing');
        }
    }

    private function className(int $idx): string
    {
        $className = $this->tokens->content($idx + 2);
        $nsBegin   = $this->tokens->findNext(0, ['T_NAMESPACE'], $idx);
        if (!$nsBegin) { return $className; }

        $nsEnd     = $this->tokens->findNext($nsBegin, ['T_SEMICOLON']);
        $namespace = $this->tokens->content($nsBegin + 2, $nsEnd - 1);
        return $namespace . '\\' . $className;
    }

    private function getAncestorMethods(string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
            $parent     = $reflection->getParentClass();
            $methods    = $parent ? $this->getMethods($parent) : [];
            $interfaces = $reflection->getInterfaces();
        } catch (Throwable $e) {
            return [];
        }
        foreach ($interfaces as $interface) {
            $methods += $this->getMethods($interface);
        }
        return $methods;
    }

    private function getMethods(ReflectionClass $class): array
    {
        $methods = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->isFinal()) { continue; }
            $methods[] = $method->getName();
        }
        return array_flip($methods);
    }
}
