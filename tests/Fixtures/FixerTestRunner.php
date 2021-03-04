<?php declare(strict_types=1);

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\Dev\Tests\Fixtures;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet;
use PhpCsFixer\Runner\Runner;
use PhpCsFixer\WhitespacesFixerConfig;
use SplFileInfo;


class FixerTestRunner
{
    private array $fixers;

    /**
     * @param FixerInterface[] $fixers
     */
    public function __construct(array $fixers)
    {
        $this->fixers = $fixers;
    }

    public static function withConfig(ConfigInterface $config): self
    {
        $fixerFactory = new FixerFactory();
        $fixerFactory->registerBuiltInFixers();
        $fixerFactory->registerCustomFixers($config->getCustomFixers());

        $fixers = $fixerFactory
            ->useRuleSet(new RuleSet($config->getRules()))
            ->setWhitespacesConfig(new WhitespacesFixerConfig($config->getIndent(), $config->getLineEnding()))
            ->getFixers();

        return new self($fixers);
    }

    /**
     * @see Runner::fixFile()
     *
     * @param string      $sourceCode
     * @param SplFileInfo $file
     *
     * @return string
     */
    public function fix(string $sourceCode, SplFileInfo $file = null): string
    {
        $file ??= new SplFileInfo(__FILE__);

        $tokens = Tokens::fromCode($sourceCode);

        foreach ($this->fixers as $fixer) {
            $this->applyFixer($tokens, $fixer, $file);
        }

        return $tokens->generateCode();
    }

    private function applyFixer(Tokens $tokens, FixerInterface $fixer, SplFileInfo $file): ?string
    {
        if (!$fixer->isCandidate($tokens)) { return null; }

        $fixer->fix($file, $tokens);

        if (!$tokens->isChanged()) { return null; }

        $tokens->clearEmptyTokens();
        $tokens->clearChanged();

        return $fixer->getName();
    }
}
