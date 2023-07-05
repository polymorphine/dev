<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Sniffer\Sniffs\NamingConventions;

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Files\File;


class ValidVariableNameSniff extends AbstractVariableSniff
{
    private File  $phpcsFile;
    private int   $varPointer;
    private array $tokens;

    protected function processVariable(File $phpcsFile, $stackPtr)
    {
        $varName = $this->contents($phpcsFile, $stackPtr);
        if (isset($this->phpReservedVars[$varName])) { return; }

        $prev = $this->phpcsFile->findPrevious([T_WHITESPACE], $stackPtr - 1, null, true);
        if ($this->tokens[$prev]['code'] === T_PAAMAYIM_NEKUDOTAYIM) {
            return;
        }

        $this->validateCamelCaps($varName, 'NotCamelCaps');
        $this->validateNumbersInName($varName, 'ContainsNumbers');
    }

    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $varName = $this->contents($phpcsFile, $stackPtr);
        $this->validateCamelCaps($varName, 'ClassVarNotCamelCaps');
        $this->validateNumbersInName($varName, 'ClassVarContainsNumbers');
    }

    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {
        $varPattern = '#[^\\\]\${?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)#';
        $contents   = $this->contents($phpcsFile, $stackPtr, false);
        if (!preg_match_all($varPattern, $contents, $matches)) { return; }

        foreach ($matches[1] as $varName) {
            if (isset($this->phpReservedVars[$varName])) { continue; }
            $this->validateCamelCaps($varName, 'StringVarNotCamelCaps');
            $this->validateNumbersInName($varName, 'StringVarContainsNumbers');
        }
    }

    private function contents(File $phpcsFile, int $varPointer, bool $isVar = true): string
    {
        $this->varPointer = $varPointer;
        if (!isset($this->file) || $this->file !== $phpcsFile) {
            $this->phpcsFile = $phpcsFile;
            $this->tokens    = $phpcsFile->getTokens();
        }
        $contents = $this->tokens[$this->varPointer]['content'];
        return $isVar ? ltrim($contents, '$') : $contents;
    }

    private function validateCamelCaps(string $varName, string $type): void
    {
        if (Common::isCamelCaps($varName, false, true, false)) { return; }

        $message = 'Variable `%s` is not in valid camel caps format';
        $this->phpcsFile->addError($message, $this->varPointer, $type, [$varName]);
    }

    private function validateNumbersInName($varName, string $type): void
    {
        if (preg_match('#[0-9]#', $varName) !== 1) { return; }
        if ($type === 'ClassVarContainsNumbers') {
            $message = 'Variable `%s` cannot contain numbers';
            $this->phpcsFile->addError($message, $this->varPointer, $type, [$varName]);
            return;
        }

        $message = 'Variable `%s` should not contain numbers';
        $this->phpcsFile->addWarning($message, $this->varPointer, $type, [$varName]);
    }
}
