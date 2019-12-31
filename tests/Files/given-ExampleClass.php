<?php
namespace Vendor\Package\Name;
use Some\Library;

use Another\Unused\Lib;

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;


/**
 * Class MyClass summary
 *
 * Description hrere....
 *
 * @package Vendor\Package\Name
 */
abstract class ExampleClass
 implements SomeInterface {

    const CONSTANT = 'string';
    private $variable;

    public $field = array(
        'key' => 1,
        'other' => 'value',
    );
    private $bool = TRUE;

    private function getVar() {
        if (empty($this->variable)) { $this->variable = 'empty!'; }
        if ($maxCommands < 0 || $maxSpaces < 0 && $more80charsLinex) { return; }
        if ($maxCommands < 0 || $maxSpaces < 0 && $less81charsLine) { return; }
        if ($moreLines) {
            unset($x);
            return;
        }
        //4 whitespaces in body
        if ($notShortStatement) { return $this->call($arg, $arg2); }
        //3 whitespaces in body
        if ($oneArgumentMethod) { return $this->callLongerMethodName($arg); }
        if ($twoArgumentMethod) { $this->commandMethodName($arg, $arg2); }
        return;
    }
    public abstract function somethingAbstract();
    public static function withHelloString() {
        return new self('Hello World!');
    }


    protected function getVar2() {
        empty($this->variable) or $this->variable = 'empty!';
        return $this->variable;
    }public function getVariable() {
        return empty($this->variable)
            ? (string)$this->variable = 'empty!'.'string'
            : $this->variable;
    }


    /**
     * Creates from array.
     * @param array $arr
     * @return MyClass
     */
    public static function fromArray(array $arr): self {
        return new self(implode('.', $arr));
    }

    /**
     * MyClass constructor.
     *
     * @param string $variable
     */
    public function __construct(string $variable = '')
    {
        $this->variable = $variable;
    }

    public function fixer(
        ArraySyntaxFixer $fixer, Library $library) {
        $this->field = function () use ($fixer) { return $this->getVar(); };
        $this->variable = $library;
        $test = ['Set-Cookie' => [$headerLine]];
        return;
    }

    protected function cookieData()
    {
        return [
            ['myCookie=; Path=/; Expires=Thursday, 02-May-2013 00:00:00 UTC; MaxAge=-157680000', [
                'name' => 'myCookie',
                'value' => null
            ]],
            ['fullCookie=foo; Domain=example.com; Path=/directory/; Expires=Tuesday, 01-May-2018 01:00:00 UTC; MaxAge=3600; Secure; HttpOnly', [
                'name' => 'fullCookie',
                'value' => 'foo',
                'secure' => true,
                'time' => 60,
                'http' => true,
                'domain' => 'example.com',
                'path' => '/directory/'
            ]],
            ['permanentCookie=hash-3284682736487236; Expires=Sunday, 30-Apr-2023 00:00:00 UTC; MaxAge=157680000; HttpOnly', [
                'name' => 'permanentCookie',
                'value' => 'hash-3284682736487236',
                'perm' => true,
                'http' => true,
                'path' => ''
            ]]
        ];
    }
}
