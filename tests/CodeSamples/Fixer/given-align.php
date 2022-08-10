<?php
$next = 'next';
$object = new stdClass();
$object->first = 1;
$object->second = 2;
$object->thirdValue = 3;

$test = [];

name($x = 'test');
name($test[] = null);
name($foo[] = 'foo bar baz');

$var = 'string';
$more = 123;
$arr['long key value'] = 'value';
$array['third'] = 5;
$arr[22] = 1;
$val = true;
$x = $next
    ? 1
    : 0;
$another = 10;
return $next = 400;

class Some
{
    public const VAR = '20';
    public $var = 10;
    public const VARIABLE = 'foo bar baz';
    protected $some = 23;
    private $test = 22;
    protected $x = true;

    public static $stat;
    public static $fooBar;

    public function foo($var)
    {
        Some::$stat = 10;
        Some::$fooBar = 20;
        $var[] = function () { return 'Hello World!'; };
        $another[] = null;
        $here['leave'] = 'super';
        $here['something'] = function () {
            return 'Hello World!';
        };
    }

    public function func()
    {
        self::$stat = 10;
        self::$fooBar = 20;
        $this->foo($default = 'test');
        $this->foo($var = 1);
        $this->foo($longNameVariable = 2);
    }
}

function name($var)
{
}
