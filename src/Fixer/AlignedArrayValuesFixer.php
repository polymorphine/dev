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
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\CT;
use SplFileInfo;


final class AlignedArrayValuesFixer implements FixerInterface
{
    use FixerMethods;

    private array $groups = [];
    private array $group  = [];

    public function getName(): string
    {
        return 'Polymorphine/aligned_array_values';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition('Aligns arrow operator in multiline arrays.', []);
    }

    public function isCandidate(Tokens $tokens): bool
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

    public function fix(SplFileInfo $file, Tokens $tokens): void
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
