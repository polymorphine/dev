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
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


final class ExtraBlankLinesFixer implements FixerInterface
{
    public function getName(): string
    {
        return 'Polymorphine/extra_blank_lines';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Adds second line break before class definition.', []);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return -40;
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $idx = $tokens->getSize() - 1;
        while ($idx = $tokens->getPrevTokenOfKind($idx, [[T_WHITESPACE]])) {
            if (!$this->hasLineBreak($tokens[$idx])) { continue; }
            if ($this->isClassDef($idx + 1, $tokens)) {
                $def = $idx + 1;
                $idx = $tokens->getPrevMeaningfulToken($idx);
                $this->fixBeforeClass($idx, $def, $tokens);
                continue;
            }

            $content = $tokens[$idx]->getContent();
            $parts   = explode("\n", $content);
            $max     = $this->isTagLineBreak($tokens[$idx - 1]) ? 1 : 2;
            $lines   = count($parts) - 1;

            $correctLinebreaks = $lines <= $max && substr($content, 0, $lines) === str_repeat("\n", $lines);
            if ($correctLinebreaks) { continue; }

            $tokens[$idx] = new Token([T_WHITESPACE, str_repeat("\n", min($lines, $max)) . $parts[$lines]]);
        }
    }

    private function isClassDef(int $idx, Tokens $tokens): bool
    {
        $isClass = static fn (int $idx): bool => $tokens[$idx]->isGivenKind([T_TRAIT, T_INTERFACE, T_CLASS]);
        return $isClass($idx) || $tokens[$idx]->isGivenKind([T_FINAL, T_ABSTRACT]) && $isClass($idx + 2);
    }

    private function fixBeforeClass(int $idx, int $def, Tokens $tokens): void
    {
        $doubleBlank = new Token([T_WHITESPACE, "\n\n\n"]);
        while ($idx++ < $def) {
            if (!$this->hasLineBreak($tokens[$idx])) { continue; }
            $tokens[$idx] = $doubleBlank;
            break;
        }

        while ($idx++ < $def) {
            $spacingWhitespace = $tokens[$idx]->isWhitespace() && $tokens[$idx]->getContent() !== "\n";
            if (!$spacingWhitespace) { continue; }
            $tokens[$idx] = new Token([T_WHITESPACE, "\n"]);
        }
    }

    private function hasLineBreak(Token $token): bool
    {
        return $token->isWhitespace() && strpos($token->getContent(), "\n") !== false;
    }

    private function isTagLineBreak(Token $token): bool
    {
        return $token->isGivenKind(T_OPEN_TAG) && substr($token->getContent(), -1) === "\n";
    }
}
