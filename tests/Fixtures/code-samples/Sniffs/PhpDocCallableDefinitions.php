<?php

namespace Some\NamespaceX;

use Closure;

class PhpDocCallableDefinitions
{
    /**
     * @param callable $callback not definition
     * @param callable() $callback
     * @return callable: void
     * @param Closure $callback   not definition
     * @return Closure() not definition
     * @param Closure: Test $noDescription
     * @param callable    $spacedCallback Closure(Type): null|array
     * @return callable callable(Type) => bool
     * @param Closure  $short  fn(\typeOne, ?int) => Namespace\SomeOtherType
     * @return Closure  fn(Something\NameSpace) => Type
     * @param callable $longDefinition       function(bool, Some\Class): Type|SomethingElse|array
     * @return callable function(Type): bool
     * @param Some\Type|Closure $long function(\typeOne, int|float, ?Third): ?Namespace\SomeOtherType
     * @return callable(Foo, ...int): \Test variadic should be last
     * @return Closure  function(?Something\NameSpace): Type
     * @param callable|null $longDefinition       function(bool, Some\Class): Type
     * @return Closure|null  fn(?Something\NameSpace) => ?Type\Valid
     * @param callable[]|null $callback fn(Type) => bool
     * @return Closure[]|null fn(Type) => bool
     */
    public function incorrectCallbacks()
    {
    }

    /**
     * @return Some\Type\Closure unknown Closure
     * @return null|Some\Type\Closure unknown Closure
     * @param Type\With\Namespace $value
     * @param int     $numberVariable
     * @param bool $param
     * @param Foo\Closure   $param  Return unknown param
     * @return int
     */
    public function ignoredPhpDocs(): void
    {
    }

    /**
     * @return callable(): void correct definition
     * @return callable(Foo): Test correct definition
     * @return callable(null|Foo): Test correct definition
     * @return callable(Foo, Bar): Namespaced\Test correct definition
     * @return callable(Foo, int...): \Test correct definition
     * @return null|callable(): Test correct definition
     * @return callable(null|Foo, int): null|Test correct definition
     * @return Closure(): void correct definition
     * @return Some\Type|Closure(Foo): Test correct definition
     * @return Closure(null|Foo): Test correct definition
     * @return Closure(Foo, Bar): Namespaced\Test correct definition
     * @return Closure(Foo, int...): \Test correct definition
     * @return null|Closure(): Test correct definition
     * @return Closure(null|Foo, int): null|Test correct definition
     * @return Closure(all, types, combined...): bool Description of returned callaback
     *
     * @return void
     */
    public function correctReturnCallbacks(): void
    {
    }

    /**
     * @param callable(): void $function correct definition
     * @param callable(Foo): Test $function correct definition
     * @param callable(null|Foo): Test $function correct definition
     * @param callable(Foo, Bar): Namespaced\Test $function correct definition
     * @param callable(Foo, int...): \Test $function correct definition
     * @param null|callable(): Test $function correct definition
     * @param callable(null|Foo, int): null|Test $function correct definition
     * @param Closure(): void $closure correct definition
     * @param Some\Type|Closure(Foo): Test $closure correct definition
     * @param Closure(null|Foo): Test $closure correct definition
     * @param Closure(Foo, Bar): Namespaced\Test $closure correct definition
     * @param Closure(Foo, int...): \Test $closure correct definition
     * @param null|Closure(): Test $closure correct definition
     * @param Closure(null|Foo, int): null|Test $closure correct definition
     * @param Closure(all, types, combined...): bool $closure Description of returned callaback
     *
     * @return array<string, mixed>
     */
    public function correctParamCallback(callable $function, Closure $closure): array
    {
    }
}
