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
    private const WARNING = 'Callable description should contain definition';

    private const FORMAT_EXPLAIN = [
        'short' => 'fn(ArgType,...) => ReturnType',
        'long'  => 'function(ArgType,...): ReturnType'
    ];

    private const FORMAT_REGEXP = [
        'short' => '#fn\([?a-zA-Z\\\\, |]*\) => \??[a-zA-Z\\\\|]+#',
        'long'  => '#function\([?a-zA-Z\\\\, |]*\): \??[a-zA-Z\\\\|]+#'
    ];

    public string $syntax         = 'both';
    public bool   $includeClosure = true;

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
                $phpcsFile->addWarning($this->warningMessage(), $stackPtr, 'Found');
            }
        }
    }

    private function validDescription(string $line, bool $forArgument): bool
    {
        if (!$this->isLambda($line)) { return true; }

        $varStart         = $forArgument ? strpos($line, '$', 8) : 1;
        $descriptionStart = $varStart ? strpos($line, ' ', $varStart) : 0;
        $description      = $descriptionStart ? trim(substr($line, $descriptionStart)) : '';
        if (!$description) { return false; }

        $selected = self::FORMAT_REGEXP[$this->syntax] ?? null;
        $patterns = $selected ? [$selected] : array_values(self::FORMAT_REGEXP);
        foreach ($patterns as $pattern) {
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

        return $type === 'callable' || $this->includeClosure && $type === 'Closure';
    }

    private function warningMessage(): string
    {
        $selected = self::FORMAT_EXPLAIN[$this->syntax] ?? null;
        $format   = $selected ?: implode('` or `', self::FORMAT_EXPLAIN);
        return self::WARNING . ' [format: `' . $format . '`]';
    }
}
