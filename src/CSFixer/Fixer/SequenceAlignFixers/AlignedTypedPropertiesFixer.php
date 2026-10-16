<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CSFixer\Fixer\SequenceAlignFixers;

use Polymorphine\Dev\CSFixer\Fixer\SequenceAlignFixers;
use PhpCsFixer\Tokenizer\Tokens;


final class AlignedTypedPropertiesFixer extends SequenceAlignFixers
{
    protected string $name       = 'Polymorphine/aligned_typed_properties';
    protected string $definition = 'Aligns variables in class declarations.';

    public function getPriority(): int
    {
        return -39;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_CLASS, T_TRAIT]);
    }

    protected function nextSequence(int $idx): ?Sequence
    {
        $idx = $this->tokens->getNextTokenOfKind($idx, [[T_PRIVATE], [T_PROTECTED], [T_PUBLIC]]);
        if (!$idx) { return null; }

        $end       = $this->tokens->getNextTokenOfKind($idx, [[T_VARIABLE], [T_FUNCTION], [T_CONST]]);
        $signature = $this->tokens[$end]->isGivenKind(T_VARIABLE) ? $this->tokenSignature($idx, $end) : [];
        return new Sequence($this->tokens, $end, $signature);
    }

    protected function alignIndex(int $idx): int
    {
        $start = $this->tokens->getPrevTokenOfKind($idx, [[T_PRIVATE], [T_PROTECTED], [T_PUBLIC]]);
        return $start ? $this->indentationPointLength($start + 2, $idx - 1) : 0;
    }

    private function tokenSignature(int $idx, int $end): array
    {
        $tokenIds[] = $this->tokens[$idx]->getId();
        while ($idx = $this->tokens->getNextMeaningfulToken($idx)) {
            if ($idx === $end) { return []; }
            if (!$this->tokens[$idx]->isGivenKind(T_STATIC)) { break; }
            $tokenIds[] = $this->tokens[$idx]->getId();
        }

        $tokenIds[] = T_STRING;
        return $tokenIds;
    }
}
