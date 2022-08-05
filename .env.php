<?php
    date_default_timezone_set('Europe/Athens');
    return [
        'settings' => [
            'environment' => $_ENV['APP_ENVIRONMENT'] ?? 'production',
            'display_error_details' => $_ENV['IS_DEVELOPMENT'] ?? false,
            'is_development' => $_ENV['IS_DEVELOPMENT'] ?? false,
        ],
        'postgresql' => [
            'db_driver' => 'pgsql',
            'db_host' => $_ENV['POSTGRES_HOST'],
            'db_username' => $_ENV['POSTGRES_USER'],
            'db_password' => $_ENV['POSTGRES_PASSWORD'],
            'db_database' => $_ENV['POSTGRES_DB'],
            'db_port' => $_ENV['POSTGRES_PORT'],
        ],
        'redis_conf' => [
            'dsn' => $_ENV['REDIS_DSN'] ?? false,
            'scheme' => $_ENV['REDIS_SCHEME'],
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'password' => $_ENV['REDIS_PASSWORD'],
        ],
        'papertrail_conf' => [
            'host' => $_ENV['PAPERTRAIL_HOST'] ?? false,
            'port' => $_ENV['PAPERTRAIL_PORT'] ?? false,
            'prefix' => $_ENV['PAPERTRAIL_PREFIX'] ?? false,
            'defaultLoggerName' => $_ENV['PAPERTRAIL_DEFAULT_LOGGER_NAME'] ?? false,
        ],
        'import' => [
            'import_data_file' => $_ENV['IMPORT_DATA_FILE'],
        ],
        'limiter' => [
            'requests_per_minute' => 10,
        ],
    ];