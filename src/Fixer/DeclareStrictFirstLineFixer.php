<?php

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


class DeclareStrictFirstLineFixer implements FixerInterface
{
    public function getName()
    {
        return 'Polymorphine/declare_strict_first_line';
    }

    public function getPriority()
    {
        return -40;
    }

    public function isRisky()
    {
        return false;
    }

    public function supports(SplFileInfo $file)
    {
        return true;
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens[0]->getContent() === "<?php\n" && $tokens->isTokenKindFound(T_DECLARE);
    }

    public function fix(SplFileInfo $file, Tokens $tokens)
    {
        $idx = $tokens->getNextTokenOfKind(0, [[T_DECLARE]]);
        if ($idx === 1) { return; }

        $directive = $tokens[$idx + 2]->getContent();
        if ($directive !== 'strict_types') { return; }

        $tokens[0] = new Token([T_OPEN_TAG, '<?php ']);

        $expression = [];
        while (!$tokens[$idx]->isWhitespace()) {
            $expression[] = $tokens[$idx];
            $tokens[$idx] = new Token('');
            $idx++;
        }

        $tokens->insertAt(1, $expression);
    }
}
