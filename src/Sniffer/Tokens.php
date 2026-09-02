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


class Tokens
{
    private array $tokens;

    /**
     * @param array<int, array<string, mixed>> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @param int $idx
     * @param int $endIdx
     *
     * @return string
     */
    public function content(int $idx, int $endIdx = 0): string
    {
        $endIdx  = max($endIdx, $idx);
        $content = '';
        while ($idx <= $endIdx && $value = $this->tokens[$idx]['content'] ?? '') {
            $content .= $value;
            $idx++;
        }
        return $content;
    }

    /**
     * @param int    $idx
     * @param string $type T_NAME code string
     *
     * @return bool
     */
    public function isType(int $idx, string $type): bool
    {
        return ($this->tokens[$idx]['type'] ?? null) === $type;
    }

    /**
     * @param int           $idx
     * @param array<string> $types  T_NAME code or content strings
     * @param int           $endIdx Index where searching should stop (expected to be higher than $idx)
     *
     * @return null|int
     */
    public function findNext(int $idx, array $types, int $endIdx = 0): ?int
    {
        return $this->scanFor($idx, $types, 1, $endIdx ? max($endIdx, $idx) : count($this->tokens));
    }

    /**
     * @param int           $idx
     * @param array<string> $types  T_NAME code or content strings
     * @param int           $endIdx Index where searching should stop (expected to be lower than $idx)
     *
     * @return null|int
     */
    public function findPrev(int $idx, array $types, int $endIdx = 0): ?int
    {
        return $this->scanFor($idx, $types, -1, min($endIdx, $idx));
    }

    private function scanFor(int $idx, array $types, int $step, int $endIdx): ?int
    {
        $contentTypes = array_filter($types, fn (string $type): bool => substr($type, 0, 2) !== 'T_');
        $typeNames    = array_diff($types, $contentTypes);

        while ($token = $this->tokens[$idx += $step] ?? null) {
            $isEndReached = ($endIdx - $idx) * $step < 0;
            if ($isEndReached) { return null; }
            $isTypeFound    = in_array($token['type'], $typeNames, true);
            $isContentFound = !$isTypeFound && in_array($token['content'], $contentTypes, true);
            if ($isTypeFound || $isContentFound) { return $idx; }
        }
        return null;
    }
}
