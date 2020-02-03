<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Sniffer\Sniffs\Arrays;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;


class AmbiguousAssociativitySniff implements Sniff
{
    private $tokens;

    public function register()
    {
        return [T_OPEN_SHORT_ARRAY];
    }

    public function process(File $file, $idx)
    {
        $this->tokens = $file->getTokens();
        if ($this->isValidArray($file, $idx)) { return; }
        $file->addWarning('Array should be either associative or list of values', $idx, 'Found');
    }

    private function isValidArray(File $file, int $idx): bool
    {
        $assoc    = false;
        $expected = null;
        while ($idx = $file->findNext([T_DOUBLE_ARROW, T_COMMA, T_OPEN_SHORT_ARRAY, T_CLOSE_SHORT_ARRAY], ++$idx)) {
            $type = $this->tokens[$idx]['code'];
            switch ($type) {
                case T_CLOSE_SHORT_ARRAY:
                    break 2;
                case T_OPEN_SHORT_ARRAY:
                    $idx = $this->tokens[$idx]['bracket_closer'];
                    break;
                case T_COMMA:
                    if (isset($this->tokens[$idx]['nested_parenthesis'])) {
                        $idx = array_values($this->tokens[$idx]['nested_parenthesis'])[0];
                        break;
                    }
                    if ($expected === T_DOUBLE_ARROW) { return false; }
                    $expected = $assoc ? T_DOUBLE_ARROW : T_COMMA;
                    break;
                case T_DOUBLE_ARROW:
                    if ($expected === T_COMMA) { return false; }
                    $assoc    = true;
                    $expected = T_COMMA;
                    break;
            }
        }
        return $expected === T_DOUBLE_ARROW ? $this->isTrailingComma($idx) : true;
    }

    private function isTrailingComma(int $idx): bool
    {
        while ($this->tokens[--$idx]['code'] !== T_COMMA) {
            if ($this->tokens[$idx]['code'] !== T_WHITESPACE) { return false; }
        }
        return true;
    }
}
