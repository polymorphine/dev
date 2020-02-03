<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\CT;
use SplFileInfo;


final class AlignedArrayValuesFixer implements FixerInterface
{
    use FixerMethods;

    private $groups = [];
    private $group  = [];

    public function getName()
    {
        return 'Polymorphine/aligned_array_values';
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_DOUBLE_ARROW);
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

    public function fix(SplFileInfo $file, Tokens $tokens)
    {
        $this->tokens = $tokens;

        $idx = 0;
        while ($array = $this->findArrayContext($idx)) {
            $array->alignIndentation();
            $idx = $array->lastTokenIdx();
        }
    }

    private function findArrayContext(int $idx): ?ArrayContext
    {
        $start = $this->tokens->getNextTokenOfKind($idx, [[CT::T_ARRAY_SQUARE_BRACE_OPEN]]);
        return $start ? new ArrayContext($this->tokens, $start) : null;
    }
}
