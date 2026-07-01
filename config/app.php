<?php
return [
    'name'         => $_ENV['APP_NAME']  ?? 'Tamil News Portal',
    'url'          => $_ENV['APP_URL']   ?? 'http://localhost/thinathulir',
    'env'          => $_ENV['APP_ENV']   ?? 'production',
    'debug'        => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'timezone'     => 'Asia/Kolkata',
    'locale'       => 'ta',
    'admin_prefix' => 'admin',

    'session' => [
        'name'     => 'tn_session',
        'lifetime' => 7200,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    'upload' => [
        'max_size' => 5 * 1024 * 1024,
        'allowed'  => ['image/jpeg','image/png','image/webp','image/gif'],
        'path'     => __DIR__ . '/../public/uploads/',
        'url_path' => '/uploads/',
    ],

    'pagination' => [
        'per_page' => 20,
    ],

    'cache' => [
        'path' => __DIR__ . '/../storage/cache/',
    ],

    'log' => [
        'path' => __DIR__ . '/../storage/logs/',
    ],
];
