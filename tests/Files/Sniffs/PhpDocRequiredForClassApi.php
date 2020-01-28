<?php


namespace Polymorphine\CodeStandards\Tests\Files\Sniffs;


class PhpDocRequiredForClassApi
{
    public $value;

    public function testA() {}
    private function testB() {}

    /** no warning in next line */
    public function testC() {}
}
