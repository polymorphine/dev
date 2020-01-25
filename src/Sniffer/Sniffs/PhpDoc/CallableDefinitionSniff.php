<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Polymorphine\CodeStandards\Sniffer\Sniffs\PhpDoc;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;


class CallableDefinitionSniff implements Sniff
{
    public $syntax;
    public $includeClosure = true;

    private $regexp = [
        'short' => '#fn\([a-zA-Z\\\\, ]*\) => [a-zA-Z\\\\]+#',
        'long'  => '#function\([a-zA-Z\\\\, ]*\): [a-zA-Z\\\\]+#'
    ];

    public function register()
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $file, $idx)
    {
        $tokens = $file->getTokens();
        while ($idx = $file->findNext(['PHPCS_T_DOC_COMMENT_TAG'], ++$idx)) {
            $tag = $tokens[$idx]['content'];
            if ($tag !== '@param' && $tag !== '@return') { continue; }

            if (!$this->validDescription($tokens[$idx + 2]['content'], $tag === '@param')) {
                $file->addWarning('Callable param description should contain definition', $idx, 'Found');
            }
        }
    }

    private function validDescription(string $line, bool $variable = true): bool
    {
        if (!$this->isLambda($line)) { return true; }

        $varStart         = $variable ? strpos($line, '$', 8) : 1;
        $descriptionStart = $varStart ? strpos($line, ' ', $varStart) : 0;
        $description      = $descriptionStart ? trim(substr($line, $descriptionStart)) : '';
        if (!$description) { return false; }

        if (isset($this->syntax, $this->regexp[$this->syntax])) {
            return (bool) preg_match($this->regexp[$this->syntax], $description);
        }

        foreach ($this->regexp as $syntax => $pattern) {
            if (preg_match($pattern, $description)) { return true; }
        }

        return false;
    }

    private function isLambda(string $line): bool
    {
        $typeEnd = strpos($line, ' ');
        $type    = $typeEnd ? substr($line, 0, $typeEnd) : $line;
        return $type === 'callable' || ($this->includeClosure && $type === 'Closure');
    }
}
