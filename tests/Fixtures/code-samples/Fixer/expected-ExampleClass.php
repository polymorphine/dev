<?php declare(strict_types=1);

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
 * Class MyClass summary.
 *
 * Description hrere....
 */
abstract class ExampleClass implements SomeInterface
{
    public const CONSTANT = 'string';

    public array $field = [
        'key'   => 1,
        'other' => 'value'
    ];
    private int  $variable = 2000;
    private bool $bool     = true;

    /**
     * MyClass constructor.
     *
     * @param string $variable
     */
    public function __construct(string $variable = '')
    {
        $this->variable = $variable;
    }

    /**
     * Creates from array.
     *
     * @param array $arr
     *
     * @return MyClass
     */
    public static function fromArray(array $arr): self
    {
        return new self(implode('.', $arr));
    }

    abstract public function somethingAbstract();

    // Non-constructor method - no return type
    public static function withHelloString()
    {
        return new self('Hello World!');
    }

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
        $test = ['Set-Cookie' => [$headerLine]];
    }

    public function anotherFixer(
        ArraySyntaxFixer $fixer,
        Library $library
    ) {
        return true;
    }

    protected function getVar2()
    {
        empty($this->variable) or $this->variable = 'empty!';
        return $this->variable;
    }

    protected function cookieData()
    {
        return [
            ['myCookie=; Path=/; Expires=Thursday, 02-May-2013 00:00:00 UTC; MaxAge=-157680000', [
                'name'  => 'myCookie',
                'value' => null
            ]],
            ['fullCookie=foo; Domain=example.com; Path=/directory/; Expires=Tuesday, 01-May-2018 01:00:00 UTC; MaxAge=3600; Secure; HttpOnly', [
                'name'   => 'fullCookie',
                'value'  => 'foo',
                'secure' => true,
                'time'   => 60,
                'http'   => true,
                'domain' => 'example.com',
                'path'   => '/directory/'
            ]],
            ['permanentCookie=hash-3284682736487236; Expires=Sunday, 30-Apr-2023 00:00:00 UTC; MaxAge=157680000; HttpOnly', [
                'name'  => 'permanentCookie',
                'value' => 'hash-3284682736487236',
                'perm'  => true,
                'http'  => true,
                'path'  => ''
            ]]
        ];
    }

    private function getVar()
    {
        if (empty($this->variable)) {
            $this->variable = 'empty!';
        }
        if ($maxCommands < 0 || $maxSpaces < 0 && $more80charsLinex) {
            return;
        }
        if ($maxCommands < 0 || $maxSpaces < 0 && $less81charsLine) { return; }
        if ($moreLines) {
            unset($x);
            return;
        }
        // 4 whitespaces in body
        if ($notShortStatement) {
            return $this->call($arg, $arg2);
        }
        // 3 whitespaces in body
        if ($oneArgumentMethod) { return $this->callLongerMethodName($arg); }
        if ($twoArgumentMethod) { $this->commandMethodName($arg, $arg2); }
    }
}
