<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Sniffer\Sniffs\PhpDoc;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use ReflectionClass;
use ReflectionMethod;
use Throwable;


class RequiredForPublicApiSniff implements Sniff
{
    private array $tokens;

    public function register()
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $file, $idx)
    {
        $this->tokens = $file->getTokens();

        $isInterface = $this->tokens[$idx]['code'] === T_INTERFACE;
        $isOrigin    = $isInterface || $this->tokens[$idx]['code'] === T_TRAIT;

        $className       = $isOrigin ? null : $this->getClassName($idx, $file);
        $ancestorMethods = $className ? $this->getAncestorMethods($className) : [];

        while ($idx = $file->findNext([T_FUNCTION], ++$idx)) {
            $lineBreak = $this->previousLineBreak($idx);
            $isApi     = $isInterface || $this->isBeforePublic($lineBreak + 1);
            if (!$isApi) { continue; }

            if ($ancestorMethods) {
                $methodName = $this->tokens[$idx + 2]['content'];
                if (isset($ancestorMethods[$methodName])) { continue; }
            }

            $expectedDocEnd = $this->tokens[$lineBreak - 1]['type'];
            if ($expectedDocEnd !== 'T_DOC_COMMENT_CLOSE_TAG') {
                $file->addWarning('Missing phpDoc comment for original public method signature', $idx, 'Missing');
            }
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

    private function isBeforePublic(int $idx): bool
    {
        $searchNext = [T_PUBLIC, T_PRIVATE, T_PROTECTED, T_FUNCTION];
        while (!in_array($this->tokens[$idx]['code'], $searchNext, true)) {
            $idx++;
        }
        return $this->tokens[$idx]['code'] === T_PUBLIC;
    }
}
