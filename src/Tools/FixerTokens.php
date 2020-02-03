<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Tools;

use PhpCsFixer\Tokenizer\Tokens;


final class FixerTokens
{
    use ArrayDump;

    public static function dumpSourceFile(string $sourceFile, string $dumpFile = null)
    {
        self::dumpSourceCode(file_get_contents($sourceFile), $dumpFile);
    }

    public static function dumpSourceCode(string $sourceCode, ?string $dumpFile = null): void
    {
        self::dump(Tokens::fromCode($sourceCode), $dumpFile);
    }

    public static function dump(Tokens $tokens, ?string $dumpFile = null): void
    {
        $data = [];
        foreach ($tokens as $token) {
            $data[] = [
                'idx'     => $token->getId(),
                'name'    => $token->getName(),
                'content' => $token->getContent()
            ];
        }

        self::json($data, $dumpFile ?: dirname(dirname(__DIR__)) . '/temp/tokens-dump.json');
    }
}
