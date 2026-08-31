<?php

namespace Some\NamespaceX;

use Closure;

class PhpDocMalformedDefinitions
{
    /**
     * @param Foo<temp, value>> $foo wrong definition
     * @param Some/Type<> $type wrong definition
     * @param object{Foo: Bar<Test} $array wrong definition
     * @param null|Test, Value $array wrong definition
     * @param Type[value $array wrong definition
     * @param [Namespaced\Type] $array wrong definition
     * @param Type<int>, Foo> $array wrong definition
     * @return Integer{Type...>} wrong definition
     */
    public function incorrectTypes()
    {
    }
}
