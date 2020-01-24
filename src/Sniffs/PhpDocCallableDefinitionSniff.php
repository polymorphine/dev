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
    public function register()
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE];
    }

    public function process(File $file, $idx)
    {
        $tokens = $file->getTokens();

        while ($idx = $file->findNext(['PHPCS_T_DOC_COMMENT_TAG'], ++$idx)) {
            if ($tokens[$idx]['content'] !== '@param') { continue; }

            $line     = $tokens[$idx + 2]['content'];
            $isLambda = substr($line, 0, 9) === 'callable ' || substr($line, 0, 8) === 'Closure ';
            if (!$isLambda) { continue; }

            $varStart = strpos($line, '$', 8);
            if (!$varStart) {
                $file->addWarning('Missing variable in callable param definition', $idx, 'Found');
                continue;
            }

            $descriptionStart = strpos($line, ' ', $varStart);
            $description      = $descriptionStart ? trim(substr($line, $descriptionStart)) : '';
            if (!$description) {
                $file->addWarning('Missing callable param description', $idx, 'Found');
                continue;
            }

            $pattern = '#fn\([a-zA-Z\\\\, ]*\) => [a-zA-Z\\\\]+#';
            if (preg_match($pattern, $description)) { continue; }

            $pattern = '#function\([a-zA-Z\\\\, ]*\): [a-zA-Z\\\\]+#';
            if (preg_match($pattern, $description)) { continue; }

            $file->addWarning('Missing callable param description', $idx, 'Found');
        }
    }
}
