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


final class ArrayDefinitionSniff implements Sniff
{
    private const WARNING = 'Array should include internal types';

    private const FORMAT_REGEXP = '#array<[a-zA-Z\\\\|]+(, ?[a-zA-Z\\\\|]+)?>#';

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

            if (!$this->validArrayDefinition($tokens[$stackPtr + 2]['content'], $tag === '@param')) {
                $phpcsFile->addWarning($this->warningMessage(), $stackPtr, 'Found');
            }
        }
    }

    private function validArrayDefinition(string $line, bool $forArgument): bool
    {
        $type = $forArgument ? substr($line, 0, strpos($line, ' $')) : $line;

        $bracketNotation = preg_match('#\[.*?\]#', $type) === 1;
        if ($bracketNotation) { return false; }

        $array = strpos($type, 'array');
        if ($array === false) { return true; }

        $differentType = preg_match('#([a-zA-Z_]array|array[a-zA-Z_])#', $type) === 1;
        return $differentType || preg_match(self::FORMAT_REGEXP, substr($type, $array)) === 1;
    }

    private function warningMessage(): string
    {
        return self::WARNING . '  [format: `array<valueType>` or `array<keyType,valueType>`]';
    }
}
