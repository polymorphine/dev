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
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


class OpeningTagLineSpacingFixer implements FixerInterface
{
    private const STRICT_TYPES = 'declare(strict_types=1);';

    public function getName(): string
    {
        return 'Polymorphine/opening_tag_line_spacing';
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition('Moves strict types declaration to first line.', []);
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
        return $tokens->isMonolithicPhp() && !$tokens->isTokenKindFound(T_OPEN_TAG_WITH_ECHO);
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $tag = $tokens[0]->isGivenKind(T_OPEN_TAG) ? 0 : 1;

        $declare = $tokens->getNextTokenOfKind($tag, [[T_DECLARE]]);
        $strict  = $declare && $tokens->generatePartialCode($declare, $declare + 6) === self::STRICT_TYPES;
        $tagLine = substr($tokens[$tag]->getContent(), -1) === "\n";

        if ($strict) {
            $tagLine && $tokens[$tag] = new Token([T_OPEN_TAG, '<?php ']);
            $isFixed = $declare === $tag + 1;
            $isFixed || $this->moveStrictTypes($tag, $declare, $tokens);
            $nextLine  = $isFixed ? $tag + 8 : $tag + 2;
            $blankLine = new Token([T_WHITESPACE, "\n\n"]);
        } else {
            $tagLine || $tokens[$tag] = new Token([T_OPEN_TAG, "<?php\n"]);
            $nextLine  = $tag + 1;
            $blankLine = new Token([T_WHITESPACE, "\n"]);
        }

        if (!$tokens[$nextLine]->isWhitespace()) {
            $tokens->insertAt($nextLine, $blankLine);
            return;
        }

        $nextContent = $tokens->getNextNonWhitespace($nextLine);
        if ($nextContent !== $nextLine + 1) {
            $tokens->clearRange($nextLine + 1, $nextContent - 1);
        }

        if ($tokens[$nextLine]->getContent() === $blankLine->getContent()) {
            return;
        }
        $tokens[$nextLine] = $blankLine;
    }

    private function moveStrictTypes(int $tag, int $declare, Tokens $tokens): void
    {
        $tokens->clearRange($declare, $declare + ($tokens[$declare + 7]->isWhitespace() ? 7 : 6));
        $tokens->insertAt($tag + 1, Tokens::fromCode(self::STRICT_TYPES));
    }
}
