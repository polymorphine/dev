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
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer as ScopedOrderFixer;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;


class OrderedClassElementsFixer implements FixerInterface
{
    private string           $testPath;
    private ScopedOrderFixer $srcFixer;
    private ScopedOrderFixer $testFixer;

    /**
     * @param string        $testPath
     * @param array<string> $srcOrder
     * @param array<string> $testOrder
     */
    public function __construct(string $testPath, array $srcOrder, array $testOrder)
    {
        $this->testPath  = $testPath;
        $this->srcFixer  = new ScopedOrderFixer();
        $this->testFixer = new ScopedOrderFixer();

        $this->srcFixer->configure(['order' => $srcOrder, 'sort_algorithm' => 'none']);
        $this->testFixer->configure(['order' => $testOrder, 'sort_algorithm' => 'none']);
    }

    public function getName(): string
    {
        return 'Polymorphine/ordered_class_elements';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return $this->srcFixer->getDefinition();
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->srcFixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $fixer = $this->isTestClass($file, $tokens) ? $this->testFixer : $this->srcFixer;
        $fixer->fix($file, $tokens);
    }

    public function getPriority(): int
    {
        return $this->srcFixer->getPriority();
    }

    public function supports(SplFileInfo $file): bool
    {
        return $this->srcFixer->supports($file);
    }

    private function isTestClass(SplFileInfo $file, Tokens $tokens): bool
    {
        $isTestPath = strpos($file->getPathname(), $this->testPath) === 0;
        if (!$isTestPath) { return false; }
        $classIdx = $tokens->getNextTokenOfKind(0, [[T_CLASS]]);
        if (!$classIdx) { return false; }

        $className = $tokens[$classIdx + 2]->getContent();
        return substr($className, -4) === 'Test' || substr($className, -5) === 'Tests';
    }
}
