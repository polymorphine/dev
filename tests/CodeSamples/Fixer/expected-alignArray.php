<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$array['key'] = [
    'key1'            => 'value',
    'longKey'         => function () { return true; },
    'veryLongKeyName' => true,
    'another'         => 'value',
    'nested' => [
        'foo'    => true,
        'barBaz' => null
    ],
    'continued'       => ['single' => 'line', 'array' => 'values']
];

$goBonkers = [
    'next'      => true,
    'call' => function () {
        return ['nasty' => 'trick', 'multiline' => 'function'];
    },
    //skip line
    'lastLong'  => true,
    'call2' => function () {
        return [
            'multiline' => 'inside',
            'last'      => 'try'
        ];
    },
    'hereAlign' => function () { return ['single' => 'line', 'both' => 'function & array']; },
    'lastKey'   => 'Should work'
];

$x = [
    'no-params'  => ['/path/only', '/path/only', []],
    'id'         => ['/page/{#no}', '/page/4', ['no' => '4']],
    'id+slug' => ['/page/{#no}/{$title}', '/page/576/foo-bar-45', [
        'no'    => '576',
        'title' => 'foo-bar-45'
    ]],
    'literal-id' => ['/foo-{%name}', '/foo-bar5000', ['name' => 'bar5000']],
    'query'      => ['/path/and?user={#id}', '/path/and?user=938', ['id' => '938']],
    'query+path' => ['/path/user/{#id}?foo={$bar}', '/path/user/938?foo=bar-BAZ', ['id' => '938', 'bar' => 'bar-BAZ']]
];
