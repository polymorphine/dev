<?php declare(strict_types=1);

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
    private const STRICT_TYPES = 'declare(strict_types=1);';

    public function getName(): string
    {
        return 'Polymorphine/declare_strict_first_line';
    }

    public function getPriority(): int
    {
        return -39;
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens[0]->getContent() === "<?php\n" && $tokens->isTokenKindFound(T_DECLARE);
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $idx = $tokens->getNextTokenOfKind(0, [[T_DECLARE]]);
        $end = $idx + 6;

        $isDirective = $tokens->generatePartialCode($idx, $end) === self::STRICT_TYPES;
        if (!$isDirective || !$tokens[$end + 1]->isWhitespace()) { return; }

        $tokens[0] = new Token([T_OPEN_TAG, '<?php ']);
        if ($idx === 1) { return; }

        $tokens->clearRange($idx, $end + 1);
        $tokens->insertAt(1, Tokens::fromCode(self::STRICT_TYPES . "\n"));
    }
}
