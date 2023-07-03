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
use ReflectionClass;
use ReflectionMethod;
use Throwable;


final class RequiredForPublicApiSniff implements Sniff
{
    private const WARNING = 'Missing phpDoc comment for original public method signature';

    private array $tokens;

    public function register(): array
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->tokens = $phpcsFile->getTokens();

        $isInterface = $this->tokens[$stackPtr]['code'] === T_INTERFACE;
        $isOrigin    = $isInterface || $this->tokens[$stackPtr]['code'] === T_TRAIT;
        $className   = $isOrigin ? null : $this->getClassName($stackPtr, $phpcsFile);

        $undocumented = [];
        while ($stackPtr = $phpcsFile->findNext([T_FUNCTION], ++$stackPtr)) {
            if (!$isInterface && !$this->isApi($stackPtr)) { continue; }

            $lineBreak    = $this->previousLineBreak($stackPtr);
            $isDocumented = $this->tokens[$lineBreak - 1]['code'] === T_DOC_COMMENT_CLOSE_TAG;
            if ($isDocumented) { continue; }

            $undocumented[] = [$this->tokens[$stackPtr + 2]['content'], $stackPtr];
        }

        if (!$undocumented) { return; }
        $ancestorMethods = $className ? $this->getAncestorMethods($className) : [];

        foreach ($undocumented as [$methodName, $stackPtr]) {
            if (isset($ancestorMethods[$methodName])) { continue; }
            $phpcsFile->addWarning(self::WARNING, $stackPtr, 'Missing');
        }
    }

    private function getClassName(int $idx, File $file): string
    {
        $className = $this->tokens[$idx + 2]['content'];

        $idx = $file->findNext([T_NAMESPACE], 0, $idx);
        if (!$idx) { return $className; }
        $namespaceEnd = $file->findNext([T_SEMICOLON], $idx);

        $idx       = $idx + 2;
        $namespace = [];
        while ($idx < $namespaceEnd) {
            $namespace[] = $this->tokens[$idx]['content'];
            $idx++;
        }

        return implode('', $namespace) . '\\' . $className;
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

    private function previousLineBreak(int $idx): int
    {
        $previousLine = $this->tokens[$idx]['line'] - 1;
        while ($this->tokens[$idx]['line'] !== $previousLine) {
            $idx--;
        }
        return $idx;
    }

    private function isApi(int $idx): bool
    {
        return $this->tokens[$idx - 2]['code'] === T_PUBLIC;
    }
}
