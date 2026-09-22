<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Sniffer;

use InvalidArgumentException;


class ClassInfo
{
    /**
     * @return null|self Instance or null if class/interface/trait keyword cannot be found
     */
    public static function fromTokens(Tokens $tokens): ?self
    {
        $typeIdx = $tokens->findNext(0, ['T_CLASS', 'T_INTERFACE', 'T_TRAIT']);
        return $typeIdx ? new self($tokens, $typeIdx) : null;
    }

    private Tokens $tokens;
    private int    $typeIdx;

    private string $namespace;
    private array  $imports;

    /**
     * @param Tokens $tokens
     * @param int    $typeIdx Pointer to class/interface/trait keyword
     *
     * @throws InvalidArgumentException When pointer doesn't point to class keyword
     */
    public function __construct(Tokens $tokens, int $typeIdx)
    {
        if (!$tokens->isType($typeIdx, ['T_CLASS', 'T_INTERFACE', 'T_TRAIT'])) {
            throw new InvalidArgumentException('Not a class code');
        }

        $this->tokens  = $tokens;
        $this->typeIdx = $typeIdx;
    }

    /**
     * @return string Parent class FQN
     */
    public function parentName(): string
    {
        if ($this->tokens->isType($this->typeIdx, ['T_TRAIT'])) { return ''; }
        $max = $this->tokens->findNext($this->typeIdx, ['T_OPEN_CURLY_BRACKET']);
        $ext = $this->tokens->findNext($this->typeIdx, ['T_EXTENDS'], $max);
        if (!$ext) { return ''; }

        $end = $this->tokens->findNext($ext + 2, ['T_WHITESPACE', '{']) - 1;
        return $this->resolveName($this->tokens->content($ext + 2, $end));
    }

    /**
     * @return list<string> Implemented interface FQNs
     */
    public function interfaces(): array
    {
        if ($this->tokens->isType($this->typeIdx, ['T_TRAIT'])) { return []; }
        $max  = $this->tokens->findNext($this->typeIdx, ['{']);
        $impl = $this->tokens->findNext($this->typeIdx, ['T_IMPLEMENTS'], $max);

        $interfaces = [];
        while ($sepIdx = $this->tokens->findNext($impl, [',', '{'], $max)) {
            $name = trim($this->tokens->content($impl + 1, $sepIdx - 1));
            $impl = $sepIdx;
            $interfaces[] = $this->resolveName($name);
        }
        return $interfaces;
    }

    private function resolveName(string $name): string
    {
        if ($name[0] === '\\') {
            return substr($name, 1);
        }

        foreach ($this->imports ??= $this->parsedImports() as $basename => $import) {
            if (strpos($name . '\\', $basename . '\\') !== 0) { continue; }
            return $import . substr($name, strlen($basename));
        }

        $this->namespace ??= $this->parsedNamespace();
        return $this->namespace ? $this->namespace . '\\' . $name : $name;
    }

    private function parsedImports(): array
    {
        $keywordIdx = 0;
        $imports    = [];
        while ($keywordIdx = $this->tokens->findNext($keywordIdx, ['T_USE'], $this->typeIdx)) {
            $multiSep = '{';
            $prefix   = '';
            while ($sepIdx = $this->tokens->findNext($keywordIdx + 1, [';', ',', $multiSep], $this->typeIdx)) {
                $import     = trim($this->tokens->content($keywordIdx + 1, $sepIdx - 1));
                $keywordIdx = $sepIdx;
                if ($this->tokens->isType($sepIdx, ['{'])) {
                    $prefix   = $import;
                    $multiSep = '}';
                    continue;
                }
                $parts = explode('\\', $prefix . $import);
                $name  = array_pop($parts);
                [$name, $alias] = explode(' as ', $name) + [null, ''];
                $parts[] = $name;
                $imports[$alias ?: $name] = implode('\\', $parts);
                if ($this->tokens->isType($sepIdx, [';', '}'])) { break; }
            }
        }
        return $imports;
    }

    private function parsedNamespace(): string
    {
        $keywordIdx = $this->tokens->findNext(0, ['T_NAMESPACE'], $this->typeIdx);
        if (!$keywordIdx) { return ''; }
        $endIdx = $this->tokens->findNext($keywordIdx + 1, [';', 'T_WHITESPACE', '{']);
        return $keywordIdx === $endIdx - 2 ? '' : $this->tokens->content($keywordIdx + 2, $endIdx - 1);
    }
}
