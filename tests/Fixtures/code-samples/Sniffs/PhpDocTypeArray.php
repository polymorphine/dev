<?php

namespace Some\NamespaceX;

use BrokenNamingConventions\Sniffs\Category\Sniff;
use Generator;
use Exception;
use Closure;


class PhpDocTypeArray
{
    /**
     * @param array $array wrong definition
     * @param iterable $array wrong definition
     * @param array<> $array wrong definition
     * @param array{Foo} $array wrong definition
     * @param null|Iterator $array wrong definition
     * @param Type[] $array wrong definition
     * @param Namespaced\Type[] $array wrong definition
     * @param array<int, Foo[]> $array wrong definition
     * @return array<Type...> wrong definition
     * @return array{
     *             multiline: definition,
     *             without_KEY
     *         } Comment about return type
     */
    public function incorrectArrays()
    {
        /** @var LocalType|array $var */
        $var = StaticClass::getSomething();
    }

    /**
     * @return Some\Typearray unknown type
     * @return nullable_array unknown type
     * @param arrays $value not array
     * @param int     $numberVariable
     * @param bool|arrayable $param
     * @param arrays\Foo<\test>  $param  Unknown param
     * @return int
     */
    public function ignoredPhpDocs(): void
    {
    }

    /**
     * @return array<bool> correct definition
     * @return array<bool> correct definition
     * @return \Traversable<string, int> correct definition
     * @return array<test_value> correct definition
     * @return array<test, anything> correct definition
     */
    public function correctReturnArrays(): array
    {
    }

    /**
     * @param array<int> $array correct definition
     * @param array<int, string> $array correct definition
     * @param list<sting> $array correct definition
     * @param array<string, mixed> $array correct definition
     * @param Generator<int> $array correct definition
     *
     * @return array<string, mixed>
     */
    public function correctParamArrays(callable $function, Closure $closure): array
    {
    }

    /**
     * @param array{
     *            callback: Closure(string): void,
     *            index: int
     *        } $methodParam Variable definition
     *
     * @throws Exception
     *
     * @return array{
     *             strict_comparison: bool,
     *             strict_param: bool,
     *             string_implicit_backslashes: array{single_quoted: string},
     *             sniff_class: Sniff
     *         } Return type definition
     */
    public function multilineArrayDefinition(array $methodParam): array
    {
        /**
         * @var array{
         *          multiline: definition,
         *          of_local: variable
         *      } $var
         */
        $var = StaticClass::getSomething();
        return test($var);
    }
}
