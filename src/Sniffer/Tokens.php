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
     * @param int           $idx
     * @param array<string> $types T_NAME code or content strings
     *
     * @return bool true if one of provided types is matched
     */
    public function isType(int $idx, array $types): bool
    {
        $token = $this->tokens[$idx] ?? null;
        if (!$token) { return false; }
        foreach ($types as $type) {
            $value = substr($type, 0, 2) === 'T_' ? $token['type'] : $token['content'];
            if ($value === $type) { return true; }
        }
        return false;
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

    /**
     * @param int $idx index of phpDoc tag like @param, @return, @var etc
     *
     * @return string Single line tag content
     */
    public function typeDoc(int $idx): string
    {
        $isTypeTag   = $this->isType($idx, ['@param', '@return', '@var']);
        $containsDoc = $isTypeTag && $this->isType($idx + 2, ['T_DOC_COMMENT_STRING']);
        if (!$containsDoc) { return ''; }

        $endIdx  = $this->endingIdx($idx + 2);
        $content = '';
        while ($idx = $this->findNext($idx, ['T_DOC_COMMENT_STRING'], $endIdx)) {
            $content .= substr($content, -1) === ',' ? ' ' : '';
            $content .= $this->content($idx);
        }
        return $content;
    }

    private function scanFor(int $idx, array $types, int $step, int $endIdx): ?int
    {
        while (($endIdx - $idx += $step) * $step >= 0) {
            if ($this->isType($idx, $types)) { return $idx; }
        }
        return null;
    }

    private function endingIdx(int $idx): int
    {
        $found = $this->findNext($idx, ['T_DOC_COMMENT_CLOSE_TAG', 'T_DOC_COMMENT_STAR']);
        if ($found === null) { return $idx; }

        $isEndLine = $this->isType($found, ['T_DOC_COMMENT_CLOSE_TAG']) || $this->isType($found + 1, ["\n"]);
        $isNextTag = $this->isType($found + 2, ['T_DOC_COMMENT_TAG']);
        return $isEndLine || $isNextTag ? $this->findPrev($found, ['T_DOC_COMMENT_STRING']) : $this->endingIdx($found);
    }
}
