<?php

namespace Some\NamespaceX;

use Closure;

class PhpDocCallableDefinitions
{
    /**
     * @param Type\With\Namespace $value
     * @param int                          $numberVariable
     * @param bool $param
     * @return int
     * @return Some\Type\Closure
     * @param callable $callback not definition
     * @return callable not definition
     * @param callable $callback
     * @return callable
     * @param Closure $callback   not definition
     * @return Closure  not definition
     * @param Closure $noDescription
     * @return Closure
     * @param callable                     $spacedCallback       fn(Type) => array
     * @return callable fn(Type) => bool
     * @param Closure  $short  fn(\typeOne, int) => Namespace\SomeOtherType
     * @return Closure  fn(Something\NameSpace) => Type
     * @param callable $longDefinition       function(bool, Some\Class): Type
     * @return callable function(Type): bool
     * @param Closure $long function(\typeOne, int, Third): Namespace\SomeOtherType
     * @return Closure  function(Something\NameSpace): Type
     * @param callable|null $longDefinition       function(bool, Some\Class): Type
     * @return Closure|null  fn(Something\NameSpace) => Type
     * @param callable[]|null $callback fn(Type) => bool
     * @return Closure[]|null fn(Type) => bool
     */
    public function method()
    {
    }
}
