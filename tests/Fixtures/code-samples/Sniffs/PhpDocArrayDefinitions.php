<?php

namespace Some\NamespaceX;

use Closure;

class PhpDocArrayDefinitions
{
    /**
     * @param array $array wrong definition
     * @param array<> $array wrong definition
     * @param array{Foo} $array wrong definition
     * @param null|array $array wrong definition
     * @param Type[] $array wrong definition
     * @param Namespaced\Type[] $array wrong definition
     * @param array $array wrong definition
     * @return array<Type...> wrong definition
     * @return array<test_value> wrong definition
     */
    public function incorrectCallbacks()
    {
    }

    /**
     * @return Some\Typearray unknown type
     * @return nullable_array unknown type
     * @param arrays $value not array
     * @param int     $numberVariable
     * @param bool|arrayable $param
     * @param arrays\Foo  $param  Unknown param
     * @return int
     */
    public function ignoredPhpDocs(): void
    {
    }

    /**
     * @return array<bool>: correct definition
     * @return array<test,anything>: correct definition
     */
    public function correctReturnCallbacks(): array
    {
    }

    /**
     * @param array<int> $array correct definition
     * @param array<int, string> $array correct definition
     * @param array<sting> $array correct definition
     * @param array<string, mixed> $array correct definition
     * @param array<int> $array correct definition
     *
     * @return array<string, mixed>
     */
    public function correctParamCallback(callable $function, Closure $closure): array
    {
    }
}
