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
use Polymorphine\Dev\Tools\PhpDocTypeLine;


final class TypeDefinitionSniff implements Sniff
{
    private const MSG_INVALID_CALLBACK = <<<'WARNING'
        Callable type definition should contain typed signature
        format: `callable(ArgType, ...): ReturnType` or
                `Closure(ArgType, ...): ReturnType`
        WARNING;

    private const MSG_INVALID_ARRAY = <<<'WARNING'
        Array type definition should contain internal type-hints
        format: `array<keyType, valueType>` or `array<valueType>` or
                `array{keyName: valueType, ...}` or `list<valueType>`
        WARNING;

    private const MSG_MALFORMED_TYPE = <<<'WARNING'
        Malformed type definition - check matching `<>` brackets
        WARNING;

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

            $phpDoc = new PhpDocTypeLine($tokens[$stackPtr + 2]['content']);
            $type   = $phpDoc->reducedType();

            if ($this->contains($type, 'callable', 'Closure')) {
                $phpcsFile->addWarning(self::MSG_INVALID_CALLBACK, $stackPtr, 'FoundCallback');
            } elseif ($this->contains($type, '[]', 'array', 'list')) {
                $phpcsFile->addWarning(self::MSG_INVALID_ARRAY, $stackPtr, 'FoundArray');
            } elseif ($type !== 'T') {
                $phpcsFile->addWarning(self::MSG_MALFORMED_TYPE, $stackPtr, 'FoundMalformed');
            }
        }
    }

    private function contains(string $text, string ...$values): bool
    {
        foreach ($values as $value) {
            if (strpos($text, $value) !== false) { return true; }
        }
        return false;
    }
}
