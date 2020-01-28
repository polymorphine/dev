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

        while ($idx = $file->findNext([T_FUNCTION], ++$idx)) {
            $previousLineEnd = $this->previousLineBreak($idx);
            if (!$this->isBeforePublic($previousLineEnd + 1)) { continue; }
            $expectedDocEnd = $this->tokens[$previousLineEnd - 1]['type'];
            if ($expectedDocEnd !== 'T_DOC_COMMENT_CLOSE_TAG') {
                $file->addWarning('test warning', $idx, 'Found');
            }
        }
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
