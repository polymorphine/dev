<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tools;


class PhpDocTypeLine
{
    private const REGEXP_TYPE_ONLY = '#([^,:]) .+#';
    private const REGEXP_TOP_ONLY  = '#({[^{]+?}|<[^<]+?>|\([^(]*?\): [^ |()<>{}]+)#';
    private const REGEXP_NS_TYPES  = '#(^|[^a-zA-Z])(?:[a-zA-Z0-9]*\\\\)+[a-zA-Z0-9]+#';
    private const REGEXP_NAMES     = '#(^|[^a-zA-Z])([a-zA-Z0-9\-_]+)#';
    private const REGEXP_ARRAY     = '#(^|[^a-zA-Z])(?:array|list)<(T(?:, T)?)>#';
    private const REGEXP_ASSOC     = '#(^|[^a-zA-Z])array{(T: T(?:, T: T)*)}#';
    private const REGEXP_CALLBACKS = '#(^|[^a-zA-Z])(?:callable|Closure)(\(T?(?:, T)*(\.\.\.)?\): T)#';
    private const REGEXP_UNREDUCED = '#(^|[^a-zA-Z0-9\-_])(?:list|array|callable|Closure)($|[^a-zA-Z0-9\-_])#';

    private string $typeDeclaration;

    /**
     * @param string $typeDeclaration Comment line after @param or @return tag
     */
    public function __construct(string $typeDeclaration)
    {
        $this->typeDeclaration = $typeDeclaration;
    }

    /**
     * @return string Extracted variable name (for example: `$variable`)
     */
    public function variableName(): string
    {
        $varStart = strpos($this->typeDeclaration, '$');
        if ($varStart === false) { return ''; }

        $varEnd = strpos($this->typeDeclaration, ' ', $varStart);
        return $varEnd
            ? substr($this->typeDeclaration, $varStart, $varEnd - $varStart)
            : substr($this->typeDeclaration, $varStart);
    }

    /**
     * @return string Simplified type used in method signature
     */
    public function simplifiedType(): string
    {
        $typeLine = preg_replace(self::REGEXP_TYPE_ONLY, '$1', $this->typeDeclaration);
        return str_replace('null|', '?', $this->removeNestedTypes($typeLine));
    }

    /**
     * @return string Valid type declaration should be reduced to `T` value
     */
    public function reducedType(): string
    {
        $isolatedTypeLine  = preg_replace(self::REGEXP_TYPE_ONLY, '$1', $this->typeDeclaration);
        $removedNamespaces = preg_replace(self::REGEXP_NS_TYPES, '$1T', $isolatedTypeLine);
        $reducedTypeNames  = preg_replace_callback(self::REGEXP_NAMES, [$this, 'replace'], $removedNamespaces);
        return $this->reduceComplexTypes($reducedTypeNames);
    }

    private function removeNestedTypes(string $line): string
    {
        $reduced = preg_replace(self::REGEXP_TOP_ONLY, '', $line);
        return $reduced !== $line ? $this->removeNestedTypes($reduced) : $reduced;
    }

    private function reduceComplexTypes(string $line): string
    {
        $string = preg_replace_callback(self::REGEXP_ARRAY, [$this, 'replace'], $line);
        $string = preg_replace_callback(self::REGEXP_ASSOC, [$this, 'replace'], $string);
        $string = preg_replace_callback(self::REGEXP_CALLBACKS, [$this, 'replace'], $string);
        $string = str_replace(['<T, T', 'T<T>', 'T|', '|T'], ['<T', 'T', '', ''], $string);
        return $string === $line ? $string : $this->reduceComplexTypes($string);
    }

    /**
     * @param array<int, string> $matches
     *
     * @return string
     */
    private function replace(array $matches): string
    {
        $containsUnreducedType = preg_match(self::REGEXP_UNREDUCED, $matches[2]) !== 1;
        return $containsUnreducedType ? $matches[1] . 'T' : $matches[0];
    }
}
