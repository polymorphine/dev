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
    private const REGEXP_SEPARATOR = '#([^,:]) .+#';
    private const REGEXP_NS_TYPES  = '#(^|[^a-zA-Z])(?:[a-zA-Z0-9]*\\\\)+[a-zA-Z0-9]+#';
    private const REGEXP_NAMES     = '#(^|[^a-zA-Z])([a-zA-Z0-9\-_]+)#';
    private const REGEXP_ARRAY     = '#(^|[^a-zA-Z])(?:array|list)<(T(?:, T)?)>#';
    private const REGEXP_ASSOC     = '#(^|[^a-zA-Z])array{(T: T(?:, T: T)*)}#';
    private const REGEXP_CALLBACKS = '#(^|[^a-zA-Z])(?:callable|Closure)(\(T?(?:, T)*(\.\.\.)?\): T)#';

    private const COMPLEX_DEF_TYPES = ['list', 'array', 'callable', 'Closure'];

    private string $typeDeclaration;

    /**
     * @param string $typeDeclaration Comment line after @param or @return tag
     */
    public function __construct(string $typeDeclaration)
    {
        $this->typeDeclaration = $typeDeclaration;
    }

    /**
     * @return string Valid type declaration should be reduced to `T` value
     */
    public function reducedType(): string
    {
        $isolatedTypeLine  = preg_replace(self::REGEXP_SEPARATOR, '$1', $this->typeDeclaration);
        $removedNamespaces = preg_replace(self::REGEXP_NS_TYPES, '$1T', $isolatedTypeLine);
        $reducedTypeNames  = preg_replace_callback(self::REGEXP_NAMES, [$this, 'replace'], $removedNamespaces);
        return $this->reduceComplexTypes($reducedTypeNames);
    }

    private function reduceComplexTypes(string $line): string
    {
        $string = preg_replace_callback(self::REGEXP_ARRAY, [$this, 'replace'], $line);
        $string = preg_replace_callback(self::REGEXP_ASSOC, [$this, 'replace'], $string);
        $string = preg_replace_callback(self::REGEXP_CALLBACKS, [$this, 'replace'], $string);
        $string = str_replace(['T<T>', 'T|', '|T'], ['T', '', ''], $string);
        return $string === $line ? $string : $this->reduceComplexTypes($string);
    }

    /**
     * @param array<int, string> $matches
     *
     * @return string
     */
    private function replace(array $matches): string
    {
        foreach (self::COMPLEX_DEF_TYPES as $type) {
            if (strpos($matches[2], $type) !== false) { return $matches[0]; }
        }
        return $matches[1] . 'T';
    }
}
