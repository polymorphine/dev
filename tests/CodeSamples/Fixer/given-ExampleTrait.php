<?php
namespace Vendor\Package\Name;
use Some\Library;

use Another\Unused\Lib;

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;


/**
 * Trait MyClass summary
 *
 * Description here....
 *
 * @package Vendor\Package\Name
 */

trait ExampleTrait {


    private function getVar() {
        if (empty($this->variable)) { $this->variable = 'empty!'; }
        return $this->variable;
    }

    protected function getVar2() {
        empty($this->variable) or $this->variable = 'empty!';
        return $this->variable;
    }


    public function getVariable() {
        return empty($this->variable)
            ? (string)$this->variable = 'empty!'.'string'
            : $this->variable;
    }
    public function fixer(
        ArraySyntaxFixer $fixer, Library $library) {
        $this->field = function () use ($fixer) { return $this->getVar($fixer); };
        $this->variable = $library;
        return;
    }
}
