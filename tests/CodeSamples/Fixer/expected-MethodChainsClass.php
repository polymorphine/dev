<?php

/*
 * This file is part of Polymorphine/Dev package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vendor\Package\Name;

use Some\Name;
use ArrayAccess;
use Exception;


class MethodChainsClass implements ArrayAccess
{
    const TEST = 1;

    protected $inheritedValues = [
        'key'       => 'value',
        'longerKey' => 'another value'
    ];

    protected $something;

    private $variable;
    private $anotherVariable = [];

    public function __construct(string $something, Name $variable)
    {
        $this->something = $something;
        $this->variable  = $variable;
    }

    public static function withSomething(Name $variable)
    {
        return new self('string', $variable);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->anotherVariable);
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new Exception('No such key');
        }
        return $this->anotherVariable[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->anotherVariable[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->anotherVariable[$offset]);
    }

    public function crazyStuff()
    {
        return function ($var) {
            $this->call($this->uri->withHost('example.com')
                                  ->withPort(9000)
                                  ->withQuery('some=query&foo=bar'))->build();
        };
    }

    public function insaneLevel()
    {
        return function ($var) {
            $this->call($this->uri->withHost('example.com')
                                  ->withPort(9000)
                                  ->withQuery('some=query&foo=bar'))
                 ->iHopeItWillWork()
                 ->build();
        };
    }

    private function someMethod($var)
    {
        if (!$var) {
            return $this->uri
                        ->withHost('example.com')
                        ->withQuery('foo=bar&baz=qux');
        }

        $var .= ' extended';
    }

    private function chainedCalls(UriInterface $uri)
    {
        $x = $this->uri->withHost('example.com')
                       ->withPort(9000)
                       ->withQuery('some=query&foo=bar');

        return $x->test($x->nested('value'));
    }

    private function anotherChainedCalls(UriInterface $uri)
    {
        $x = $uriObject->withHost('example.com')
                       ->withPort(9000)
                       ->something()
                       ->withQuery('some=query&foo=bar');

        return $x;
    }

    private function withMultilineParams()
    {
        return $builder->route('name')
                       ->get(Pattern::string('/path'))
                       ->callback(function (Request $request) use ($container) {
                           $id   = $request->getAttribute(ATTR);
                           $html = $this->html('home', $container->get(ROUTER));

                           return Response::html($html->render([
                             'user'  => $id ? $container->get('user')->name() : null,
                             'token' => $id ? $container->get('csrf.token') : null
                           ]));
                       })
                       ->lastcall();
    }

    private function alignedMultilineParams()
    {
        return $builder->route('name')
                       ->get(Pattern::string('/path'))
                       ->callback(function (Request $request) use ($container) {
                           $id   = $request->getAttribute(ATTR);
                           $html = $this->html('home', $container->get(ROUTER));

                           return Response::html($html->render([
                               'user'  => $id ? $container->get('user')->name() : null,
                               'token' => $id ? $container->get('csrf.token') : null
                           ]));
                       })
                       ->lastcall();
    }
}
