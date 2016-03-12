<?php
return [
    'seeTasks' => [
        'type' => 2,
        'description' => 'See tasks',
    ],
    'getTools' => [
        'type' => 2,
        'description' => 'Can use tools',
    ],
    'updatePriority' => [
        'type' => 2,
        'description' => 'Can update the priority and sort tasks',
    ],
    'user' => [
        'type' => 1,
        'children' => [
            'seeTasks',
        ],
    ],
    'superuser' => [
        'type' => 1,
        'children' => [
            'updatePriority',
            'user',
        ],
    ],
    'admin' => [
        'type' => 1,
        'children' => [
            'getTools',
            'superuser',
        ],
    ],
];
