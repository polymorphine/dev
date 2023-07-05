<?php

// reserved names
echo $_SERVER['var_name'];
echo $_REQUEST['var_name'];
echo $_GET['var_name'];
echo $_POST['var_name'];
echo $GLOBALS['var_name'];

// local variables
$camelCaps = new SomeClass();
$technicallycorrect = true;
$not_camel = new SomeClass();
$Capital = 'city';
$_likePrivate = false;
$hasNumber1 = false;
$has2number = 'false';

// class members
class SomeClass
{
    public static $staticVar;
    protected static array $technicallycorrect;
    public static $not_camel = 100;
    private static $_oldSchoolPrivate;
    public static $with100numerInside = 100;
    public static $endsWithNumber6 = 6;

    public Closure $camelCaps;
    private array $_privateLike = [];
    public int $shudd3r = 3;
    public array $endsWithNumber1 = [];

    public function doSomething($not_camel = false, $has2Number = false): void
    {
        $cameCase = $not_camel && $has2Number;

        // not validated object reference
        $this->_privateLike = [$cameCase];
    }
}

$not_camel->doSomething();
$cameCase->doSomething($bad_arg_name);

// object/class referenced var names are not validated
// 1. Might be 3rd party code
// 2. Ours will fail at definition anyway
$camelCaps->endsWithNumber1 = [];
SomeClass::$not_camel = 220;

// in strings
$str = "Hello $name_or_world!";
echo 'This is first line
line with $_likePrivate variable, but in single quote
which is ' . $concatenated2;

echo "And here double ${quote}
with $Capital variable
in second line ${wrong_name}
this is $variable_name}";

$nowDoc = <<<'EOF'
Variables in $NOWDOC are not validated
EOF;

$hereDoc = <<<EOF
    But they are validated in HEREDOC - $has2Number
    This line {$should_have} error
    this is $\{not variable name.
    EOF;

$error = "format is \$GLOBALS['$varName']";

echo $_SESSION['var_name'];
echo $_FILES['var_name'];
echo $_ENV['var_name'];
echo $_COOKIE['var_name'];

$XML       = 'hello';
$myXML     = 'hello';
$XMLParser = 'hello';
$xmlParser = 'hello';

echo "{$_SERVER['HOSTNAME']} $var_name";
echo "{$_SERVER['HOSTNAME']} $varName";

var_dump($http_response_header);
var_dump($HTTP_RAW_POST_DATA);
var_dump($php_errormsg);
