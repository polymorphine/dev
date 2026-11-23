<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\CSFixer\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use Polymorphine\Dev\CSFixer\Fixer\SequenceAlignFixers\Sequence;
use Polymorphine\Dev\CSFixer\AlignTokens;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Generator;


abstract class SequenceAlignFixers implements FixerInterface
{
    protected string $name;
    protected string $definition;

    protected AlignTokens $tokens;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition($this->definition, []);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    abstract public function getPriority(): int;

    abstract public function isCandidate(Tokens $tokens): bool;

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $this->tokens = new AlignTokens($tokens);
        foreach ($this->groups() as $group) {
            $group && $this->tokens->align($group);
        }
    }

    protected function groups(): Generator
    {
        $group = [];
        $prev  = new Sequence($this->tokens, 0);
        while ($sequence = $this->nextSequence($prev->endIdx)) {
            if (!$prev->sameGroup($sequence)) {
                yield $group;
                $group = [];
            }

            $group[$sequence->endIdx] = $sequence->length();
            $prev = $sequence;
        }
        yield $group;
    }

    abstract protected function nextSequence(int $idx): ?Sequence;
}
