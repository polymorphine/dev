<?php

/*
 * This file is part of Polymorphine/CodeStandards package.
 *
 * (c) Shudd3r <q3.shudder@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$x = $argv[0] ?? 'command';

if ($x === 'command') { echo 'Allman style?'; }

    //Try something else (indentation unfixed)

echo "string with evaluated $x variable and $argv[0] variable";

function doSomething($x = 3, $a = 10)
{
    return $x + $a;
}
