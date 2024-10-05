<?php

return [
    'jwt' => [
        'secret' => 'your_jwt_secret_here',
        'expiration' => 3600, // in seconds
    ],
    'swoole' => [
        'host' => '0.0.0.0',
        'port' => 443,
        'ssl' => [
            'cert_file' => '/path/to/ssl/certificate.crt',
            'key_file' => '/path/to/ssl/private.key',
            'protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3,
            'ciphers' => 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH',
        ],
    ],
    'api_keys' => [
        'openai' => 'your_openai_api_key_here',
        'piapi' => 'your_piapi_api_key_here',
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'your_database_name',
        'username' => 'your_database_username',
        'password' => 'your_database_password',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    // Add other configuration settings as needed
];