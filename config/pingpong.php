<?php

return [
    'raspberry_url' => env(
        'RASPBERRY_PI_URL',
        'http://192.168.4.1'
    ),

    'raspberry_token' => env(
        'RASPBERRY_PI_TOKEN',
        ''
    ),

    'request_timeout' => (int) env(
        'RASPBERRY_PI_TIMEOUT',
        5
    ),
];