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
    private const WARNING = 'Callable type should contain typed signature';

    private const FORMAT_REGEXP = '#(callable|Closure)\([?a-zA-Z\\\\, |]*([a-zA-Z]...)*\): \??[a-zA-Z\\\\|]+#';

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

            if (!$this->validCallable($tokens[$stackPtr + 2]['content'], $tag === '@param')) {
                $phpcsFile->addWarning($this->warningMessage(), $stackPtr, 'Found');
            }
        }
    }

    private function validCallable(string $line, bool $forArgument): bool
    {
        $type   = $forArgument ? substr($line, 0, strpos($line, ' $')) : $line;
        $lambda = strpos($type, 'Closure');
        $lambda = $lambda === false ? strpos($type, 'callable') : $lambda;
        if ($lambda === false) { return true; }

        $incorrectType = $lambda >= 2 && preg_match('#[a-zA-Z]\\\\#', substr($type, $lambda - 2, $lambda)) === 1;
        return $incorrectType || preg_match(self::FORMAT_REGEXP, substr($type, $lambda)) === 1;
    }

    private function warningMessage(): string
    {
        return self::WARNING . '  [format: `callable(ArgType,...): ReturnType` or `Closure(ArgType,...): ReturnType`]';
    }
}
