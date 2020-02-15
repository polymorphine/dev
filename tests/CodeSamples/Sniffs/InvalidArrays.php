<?php

$assocOnly = [
    'one' => str_replace('a', 'b', $this->getString(7, 11, 12)),
    'two' => [
        'two' => 2, 'three' => 3, 'four'
    ]
];

$multipleKinds = [
    1,
    [
        'two' => 2,
        'three' => 3,
        'four' => ['five', 'six', 'seven'],
    ],
    fn ($test) => $test + 1,
];

$mixedSimple = [1, 2, 3 => 'three'];

$mixedNested = [
    1,
    [
        'two' => 2,
        'three' => 3,
        'four' => ['five', 'six' => 6, 'seven']
    ],
    3 => 'assoc'
];

$valid = foo([
    'one' => max(1, 7),
    'two' => 2
]);


foreach ($mixedNested as $key => $value) {}
