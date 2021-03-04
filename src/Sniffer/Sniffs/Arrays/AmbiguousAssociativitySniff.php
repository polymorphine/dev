<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Sniffer\Sniffs\Arrays;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;


class AmbiguousAssociativitySniff implements Sniff
{
    private array $tokens;

    public function register(): array
    {
        return [T_OPEN_SHORT_ARRAY];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->tokens = $phpcsFile->getTokens();
        if ($this->isValidArray($phpcsFile, $stackPtr)) { return; }
        $phpcsFile->addWarning('Array should be either associative or list of values', $stackPtr, 'Found');
    }

    private function isValidArray(File $file, int $idx): bool
    {
        $assoc      = false;
        $expected   = null;
        $tokenTypes = [T_DOUBLE_ARROW, T_COMMA, T_OPEN_SHORT_ARRAY, T_CLOSE_SHORT_ARRAY, T_OPEN_PARENTHESIS];
        while ($idx = $file->findNext($tokenTypes, ++$idx)) {
            $type = $this->tokens[$idx]['code'];
            switch ($type) {
                case T_OPEN_SHORT_ARRAY:
                    $idx = $this->tokens[$idx]['bracket_closer'];
                    break;
                case T_OPEN_PARENTHESIS:
                    $idx = $this->tokens[$idx]['parenthesis_closer'];
                    break;
                case T_DOUBLE_ARROW:
                    if ($expected === T_COMMA) { return false; }
                    $assoc    = true;
                    $expected = T_COMMA;
                    break;
                case T_COMMA:
                    if ($expected === T_DOUBLE_ARROW) { return false; }
                    $expected = $assoc ? T_DOUBLE_ARROW : T_COMMA;
                    break;
                case T_CLOSE_SHORT_ARRAY:
                    break 2;
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
