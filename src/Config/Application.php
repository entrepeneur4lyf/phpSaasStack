<?php

declare(strict_types=1);

return [
    'app' => [
        'env' => env('APP_ENV', 'development'),
        'debug' => [
            'enabled' => env('APP_DEBUG', true)
        ],
        'key' => env('APP_KEY'),
        'url' => env('APP_URL', 'http://localhost'),
        'error_reporting' => [
            'service' => env('ERROR_REPORTING_SERVICE', 'local'), // 'local' or 'rollbar'
            'rollbar' => [
                'access_token' => env('ROLLBAR_ACCESS_TOKEN'),
                'environment' => env('APP_ENV', 'production'),
            ],
        ],
    ],
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'expiration' => env('JWT_EXPIRATION', 3600),
    ],
    'swoole' => [
        'host' => env('SWOOLE_HOST', '0.0.0.0'),
        'port' => env('SWOOLE_PORT', 443),
        'ssl' => [
            'cert_file' => env('SWOOLE_SSL_CERT'),
            'key_file' => env('SWOOLE_SSL_KEY'),
            'protocols' => SWOOLE_SSL_TLSv1_2 | SWOOLE_SSL_TLSv1_3,
            'ciphers' => 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH',
        ],
    ],
    'cors' => [
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => false,
    ],
    'api_keys' => [
        'openai' => env('OPENAI_API_KEY'),
        'piapi' => env('PIAPI_API_KEY'),
    ],
    'database' => [
        'default' => [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'pool_size' => env('DB_POOL_SIZE', 64),
        ],
        'read' => [
            'driver' => env('DB_READ_CONNECTION', 'mysql'),
            'host' => env('DB_READ_HOST', env('DB_HOST', 'localhost')),
            'port' => env('DB_READ_PORT', env('DB_PORT', 3306)),
            'database' => env('DB_READ_DATABASE', env('DB_DATABASE')),
            'username' => env('DB_READ_USERNAME', env('DB_USERNAME')),
            'password' => env('DB_READ_PASSWORD', env('DB_PASSWORD')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'pool_size' => env('DB_READ_POOL_SIZE', 32),
        ],
        'write' => [
            'driver' => env('DB_WRITE_CONNECTION', 'mysql'),
            'host' => env('DB_WRITE_HOST', env('DB_HOST', 'localhost')),
            'port' => env('DB_WRITE_PORT', env('DB_PORT', 3306)),
            'database' => env('DB_WRITE_DATABASE', env('DB_DATABASE')),
            'username' => env('DB_WRITE_USERNAME', env('DB_USERNAME')),
            'password' => env('DB_WRITE_PASSWORD', env('DB_PASSWORD')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'pool_size' => env('DB_WRITE_POOL_SIZE', 16),
        ],
    ],
    'session' => [
        'path' => __DIR__ . '/../../storage/sessions',
        'encryption_key' => env('SESSION_ENCRYPTION_KEY'),
    ],
    'mail' => [
        'mailer' => env('MAIL_MAILER', 'smtp'),
        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'name' => env('MAIL_FROM_NAME', 'Example'),
        ],
    ],
    'logging' => [
        'channel' => env('LOG_CHANNEL', 'stack'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
    // Add the cache configuration here
    'cache' => [
        'default' => [
            'driver' => env('CACHE_DRIVER', 'redis'),
            'packer' => \Hyperf\Codec\Packer\PhpSerializerPacker::class,
            'prefix' => env('CACHE_PREFIX', 'myapp_cache:'),
        ],
    ],
    'feature_flags' => [
        'new_user_registration' => env('FEATURE_NEW_USER_REGISTRATION', true),
        'advanced_search' => env('FEATURE_ADVANCED_SEARCH', false),
        'chat_system' => env('FEATURE_CHAT_SYSTEM', false),
        // Add more feature flags as needed
    ],
    // Add the Redis configuration here
    'redis' => [
        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'auth' => env('REDIS_AUTH', null),
            'port' => (int) env('REDIS_PORT', 6379),
            'db' => (int) env('REDIS_DB', 0),
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
            ],
        ],
    ],
];