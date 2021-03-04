<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Sniffer\Sniffs\PhpDoc;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;


final class CallableDefinitionSniff implements Sniff
{
    public string $syntax;
    public bool   $includeClosure = true;

    private $regexp = [
        'short' => '#fn\([?a-zA-Z\\\\, |]*\) => \??[a-zA-Z\\\\|]+#',
        'long'  => '#function\([?a-zA-Z\\\\, |]*\): \??[a-zA-Z\\\\|]+#'
    ];

    public function register(): array
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        while ($stackPtr = $phpcsFile->findNext([T_DOC_COMMENT_TAG], ++$stackPtr)) {
            $tag = $tokens[$stackPtr]['content'];
            if ($tag !== '@param' && $tag !== '@return') { continue; }

            if (!$this->validDescription($tokens[$stackPtr + 2]['content'], $tag === '@param')) {
                $phpcsFile->addWarning('Callable param description should contain definition', $stackPtr, 'Found');
            }
        }
    }

    private function validDescription(string $line, bool $variable = true): bool
    {
        if (!$this->isLambda($line)) { return true; }

        $varStart         = $variable ? strpos($line, '$', 8) : 1;
        $descriptionStart = $varStart ? strpos($line, ' ', $varStart) : 0;
        $description      = $descriptionStart ? trim(substr($line, $descriptionStart)) : '';
        if (!$description) { return false; }

        if (isset($this->syntax, $this->regexp[$this->syntax])) {
            return (bool) preg_match($this->regexp[$this->syntax], $description);
        }

        foreach ($this->regexp as $syntax => $pattern) {
            if (preg_match($pattern, $description)) { return true; }
        }

        return false;
    }

    private function isLambda(string $line): bool
    {
        $typeEnd = strpos($line, ' ');
        $type    = $typeEnd ? substr($line, 0, $typeEnd) : $line;
        if ($alternative = strpos($type, '|')) {
            $type = substr($type, 0, $alternative);
        }

        if (substr($type, -2) === '[]') {
            $type = substr($type, 0, -2);
        }

        return $type === 'callable' || ($this->includeClosure && $type === 'Closure');
    }
}
