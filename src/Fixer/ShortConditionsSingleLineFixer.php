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


final class ShortConditionsSingleLineFixer implements FixerInterface
{
    public function getName(): string
    {
        return 'Polymorphine/short_conditions_single_line';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Converts short condition statements to single line.', []);
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_IF);
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
        foreach ($this->possibleFixes($tokens) as $idx => [$begin, $end]) {
            if ($this->singleLineLength($tokens, $idx, $begin, $end) > 80) {
                continue;
            }

            $space = new Token(' ');
            $tokens[$begin + 1] = $space;
            $tokens[$end - 1]   = $space;
        }
    }

    private function singleLineLength(Tokens $tokens, int $idx, int $bodyStart, int $bodyEnd): int
    {
        $proto  = [];
        $indent = $tokens[$idx - 1]->getContent();
        if ($pos = strrpos($indent, "\n")) {
            $indent = substr($indent, $pos + 1);
        }

        $proto[$idx - 1] = $indent;
        for ($i = $idx; $i <= $bodyEnd; $i++) {
            $proto[$i] = $tokens[$i]->getContent();
        }

        $proto[$bodyStart + 1] = ' ';
        $proto[$bodyEnd - 1]   = ' ';

        $code = implode('', $proto);

        return strlen($code);
    }

    private function possibleFixes(Tokens $tokens): array
    {
        $possibleFixes = [];
        foreach ($tokens->findGivenKind(T_IF) as $idx => $token) {
            $bodyStart = $tokens->getNextTokenOfKind($idx, ['{']);
            $bodyEnd   = $tokens->getNextTokenOfKind($bodyStart, ['}']);
            $nextOpen  = $tokens->getNextTokenOfKind($bodyStart, ['{']);
            $nested    = $nextOpen && $bodyEnd > $nextOpen;

            if ($nested) { continue; }

            if (isset($tokens[$bodyEnd + 2]) && $tokens[$bodyEnd + 2]->isGivenKind([T_ELSE, T_ELSEIF])) {
                continue;
            }

            $maxCommands = 1;
            $maxSpaces   = 3;
            $inBody      = $bodyStart;
            while ($inBody < $bodyEnd) {
                $inBody++;
                if ($tokens[$inBody]->isWhitespace()) {
                    $maxSpaces--;
                } elseif ($tokens[$inBody]->getContent() === ';') {
                    $maxCommands--;
                } elseif ($tokens[$inBody]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
                    $string = $tokens[$inBody]->getContent();
                    if (strlen($string) > 20 || strpos($string, "\n") !== false) {
                        break;
                    }
                }

                if ($maxCommands < 0 || $maxSpaces < 0) { break; }
            }

            if ($inBody !== $bodyEnd) { continue; }

            $possibleFixes[$idx] = [$bodyStart, $bodyEnd];
        }

        return $possibleFixes;
    }
}
