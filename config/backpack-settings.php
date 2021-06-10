<?php

return [
    'route_prefix' => 'settings',
    'cache' => [
        'store' => null,
        'key' => 'settings'
    ],
    'config_override' => [
        'app.support_mail' => 'main.email'
    ]
];
