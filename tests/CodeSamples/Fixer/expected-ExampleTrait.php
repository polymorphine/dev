<?php

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vendor\Package\Name;

use Some\Library;

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;


/**
 * Trait MyClass summary.
 *
 * Description here....
 */
trait ExampleTrait
{
    public function getVariable()
    {
        return empty($this->variable)
            ? (string) $this->variable = 'empty!' . 'string'
            : $this->variable;
    }

    public function fixer(
        ArraySyntaxFixer $fixer,
        Library $library
    ) {
        $this->field    = function () use ($fixer) { return $this->getVar($fixer); };
        $this->variable = $library;
    }

    protected function getVar2()
    {
        empty($this->variable) or $this->variable = 'empty!';
        return $this->variable;
    }

    private function getVar()
    {
        if (empty($this->variable)) {
            $this->variable = 'empty!';
        }
        return $this->variable;
    }
}
