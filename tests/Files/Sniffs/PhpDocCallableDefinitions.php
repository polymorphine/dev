<?php

namespace Some\NamespaceX;

use Closure;

class PhpDocCallableDefinitions
{
    /**
     * @param LongTypeName\With\NamespaceX $value
     * @param callable                     $callback       fn(Type) => array
     *                                                     further description...
     * @param int                          $numberVariable
     */
    public function doSomethingA(LongTypeName\With\NamespaceX $value, callable $callback, int $numberVariable)
    {
    }

    /**
     * @param callable $callback not definition
     * @param Closure  $closure  fn(\typeOne, int) => Namespace\SomeOtherType
     */
    public function doSomethingB(callable $callback, Closure $closure)
    {
    }

    /**
     * @param Closure $callback
     */
    public function doSomethingC($callback)
    {
    }

    /**
     * @param callable $callback       fn() => Type
     */
    public function doSomethingD(callable $callback)
    {
    }

    /**
     * @param callable $longDescription       function(bool, Some\Class): Type
     */
    public function doSomethingE(callable $longDescription)
    {
    }
}
