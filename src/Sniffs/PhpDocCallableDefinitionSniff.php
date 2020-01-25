<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Sniffs;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;


class PhpDocCallableDefinitionSniff implements Sniff
{
    public $shortSyntax     = true;
    public $longSyntax      = true;
    public $describeClosure = true;

    private $descriptionRegexp = [
        'shortSyntax' => '#fn\([a-zA-Z\\\\, ]*\) => [a-zA-Z\\\\]+#',
        'longSyntax'  => '#function\([a-zA-Z\\\\, ]*\): [a-zA-Z\\\\]+#'
    ];

    public function register()
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $file, $idx)
    {
        $tokens = $file->getTokens();
        while ($idx = $file->findNext(['PHPCS_T_DOC_COMMENT_TAG'], ++$idx)) {
            if ($tokens[$idx]['content'] !== '@param') { continue; }

            if (!$this->validDescription($tokens[$idx + 2]['content'])) {
                $file->addWarning('Callable param description should contain definition', $idx, 'Found');
            }
        }
    }

    private function validDescription(string $line): bool
    {
        if (!$this->isLambda($line)) { return true; }

        $varStart         = strpos($line, '$', 8);
        $descriptionStart = $varStart ? strpos($line, ' ', $varStart) : 0;
        $description      = $descriptionStart ? trim(substr($line, $descriptionStart)) : '';
        if (!$description) { return false; }

        foreach ($this->descriptionRegexp as $syntax => $pattern) {
            if (!$this->{$syntax}) { continue; }
            if (preg_match($pattern, $description)) { return true; }
        }

        return false;
    }

    private function isLambda(string $line): bool
    {
        $typeEnd = strpos($line, ' ');
        $type    = $typeEnd ? substr($line, 0, $typeEnd) : $line;
        return $type === 'callable' || ($this->describeClosure && $type === 'Closure');
    }
}
